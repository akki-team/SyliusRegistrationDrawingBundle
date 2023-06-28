<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Service;

use Akki\SyliusRegistrationDrawingBundle\Controller\RegistrationDrawingController;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use App\Entity\Taxonomy\Taxon;
use App\Mailer\Sender\KMSenderInterface;
use App\Repository\OrderRepositoryInterface;
use App\Service\ExportEditeur\GeneratedFileService;
use Doctrine\Persistence\ObjectRepository;
use League\Csv\ByteSequence;
use League\Csv\Writer;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ExportService
{
    /** @var ObjectRepository $registrationDrawingRepository */
    private ObjectRepository $registrationDrawingRepository;

    /** @var OrderRepositoryInterface $orderRepository */
    private OrderRepositoryInterface $orderRepository;

    /** @var RegistrationDrawingController $registrationDrawingController */
    protected RegistrationDrawingController $registrationDrawingController ;

    /** @var string $kernelProjectDir */
    protected string $kernelProjectDir;

    /** @var GeneratedFileService $generatedFileService */
    private GeneratedFileService $generatedFileService;

    /** @var KMSenderInterface $emailSender */
    private KMSenderInterface $emailSender;

    /**
     * @param ObjectRepository $registrationDrawingRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param RegistrationDrawingController $registrationDrawingController
     * @param string $kernelProjectDir
     * @param GeneratedFileService $generatedFileService
     * @param KMSenderInterface $emailSender
     */
    public function __construct
    (
        ObjectRepository $registrationDrawingRepository,
        OrderRepositoryInterface $orderRepository,
        RegistrationDrawingController $registrationDrawingController,
        string $kernelProjectDir,
        GeneratedFileService $generatedFileService,
        KMSenderInterface $emailSender
    ) {
        $this->registrationDrawingRepository = $registrationDrawingRepository;
        $this->orderRepository = $orderRepository;
        $this->registrationDrawingController = $registrationDrawingController;
        $this->kernelProjectDir = $kernelProjectDir;
        $this->generatedFileService = $generatedFileService;
        $this->emailSender = $emailSender;
    }

    /**
     * @param RegistrationDrawing $drawing
     * @param \DateTime $startDate
     * @param \DateTime $endDate
     * @param int|null $drop
     * @return void
     */
    public function exportDrawing(RegistrationDrawing $drawing, \DateTime $startDate, \DateTime $endDate, ?int $drop)
    {
        $otherDrawings = array_filter($this->registrationDrawingRepository->findAll(), function ($dr) use ($drawing) {
            return $dr !== $drawing;
        });

        $otherTitles = [];

        /** @var RegistrationDrawing $otherDrawing */
        foreach ($otherDrawings as $otherDrawing) {
            /** @var Taxon $title */
            foreach ($otherDrawing->getTitles() as $title) {
                $otherTitles[] = $title;
            }
        }

        $orders = $this->orderRepository->findAllTransmittedForDrawingExport($drawing, $startDate, $endDate, $otherTitles);

        $fileName = $drawing->getFormat() === Constants::CSV_FORMAT ? "{$drawing->getName()}_{$startDate->format('d-m-Y')}_{$endDate->format('d-m-Y')}.csv" : "{$drawing->getName()}_{$startDate->format('d-m-Y')}_{$endDate->format('d-m-Y')}.txt";
        $filePath = $this->kernelProjectDir.Constants::DIRECTORY_PUBLIC.Constants::DIRECTORY_EXPORT.$fileName;

        if (!empty($orders)) {
            $export = $this->registrationDrawingController->exportDrawing($drawing, $orders, $filePath, $otherTitles);
            $totalLines = $export[1];
            $totalCancellations = $export[2];

            if ($export[1] > 0 || $export[2] > 0) {
                if (null === $export[0]) {
                    $this->sendMail(
                        Constants::ERROR_MAIL_CODE,
                        Constants::ERROR_MAIL_RECIPIENTS,
                        ['fileName' => $fileName, 'error' => 'Erreur pendant la génération du fichier']
                    );
                }

                $drawingFirstVendor = !empty($drawing->getVendors()) ? $drawing->getVendors()->toArray()[0] : null;

                $this->generatedFileService->addFile($drawingFirstVendor, $fileName, $filePath, $startDate, $endDate, $totalLines, $totalCancellations, $drawing);

                if ($drop === 1) {
                    $success = $this->sendSalesReportToVendor($drawing, $filePath);

                    if ($success) {
                        try {
                            $this->sendMail(
                                Constants::SUCCESS_MAIL_CODE,
                                explode(';', str_replace(' ', '', $drawing->getRecipients())),
                                ['fileName' => $fileName, 'totalLines' => $totalLines, 'totalCancelled' => $totalCancellations]
                            );
                        } catch (\Exception $e) {
                            $this->sendMail(
                                Constants::ERROR_MAIL_CODE,
                                Constants::ERROR_MAIL_RECIPIENTS,
                                ['fileName' => $fileName, 'error' => $e->getMessage()]
                            );
                        }
                    }
                }
            }
        }
    }

    /**
     * @param array $header
     * @param array $lines
     * @param string $delimiter
     * @return Writer
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    public function exportCSV(array $header, array $lines, string $delimiter = ';'): Writer
    {
        $writer = Writer::createFromStream(fopen('php://temp', 'rb+'));
        $writer->setDelimiter($delimiter);
        $writer->setOutputBOM(ByteSequence::BOM_UTF8);
        $writer->insertOne($header);
        $writer->insertAll($lines);

        return $writer;
    }

    /**
     * @param array $fields
     * @return string
     */
    public function exportFixedLength(array $fields): string
    {
        $text = '';

        foreach ($fields as $field) {
            $text .= implode('', $field)."\n";
        }

        return $text;
    }

    /**
     * @param RegistrationDrawing $drawing
     * @param string $filePath
     * @param SymfonyStyle|null $outputStyle
     * @param OutputInterface|null $output
     * @return bool
     */
    public function sendSalesReportToVendor(
        RegistrationDrawing $drawing,
        string $filePath,
        SymfonyStyle $outputStyle = null,
        OutputInterface $output = null
    ): bool
    {
        $success = false;

        $rsaSrc = "~www-data/.ssh/id_rsa";
        $sendMode = $drawing->getSendMode();
        $depositAddress = $drawing->getDepositAddress();
        $user = $drawing->getUser();
        $password = $drawing->getPassword();
        $host = $drawing->getHost();
        $port = $drawing->getPort();

        // si depose SFTP
        if ($sendMode === 'SSH') {
            $command = "echo -e 'put \"$filePath\"' | sftp -o StrictHostKeyChecking=no -i $rsaSrc -P $port $user@$host:\"$depositAddress\"";
        } else {
            $lftpOption = "set sftp:connect-program 'ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no'";
            $command = "lftp -c \"$lftpOption; connect sftp://$user:$password@$host:$port;put -O '$depositAddress' '$filePath'\"";
        }

        if (!is_null($outputStyle)) {
            $outputStyle->writeln($command);
        }
        $process = Process::fromShellCommandline($command);

        try {
            $process->run() ;
        } catch (\Exception $e) {
            if (!is_null($outputStyle)) {
                $outputStyle->writeln("Erreur pendant la depose SFTP : ".$e->getMessage());
            }

            $this->sendMail(
                Constants::ERROR_MAIL_CODE,
                Constants::ERROR_MAIL_RECIPIENTS,
                ['fileName' => basename($filePath), 'error' => $e->getMessage()]
            );
        } finally {
            if (!$process->isSuccessful()) {
                if (!is_null($outputStyle)) {
                    $outputStyle->writeln("Erreur pendant la depose SFTP");
                }
            } else {
                $success = true;
                if (!is_null($outputStyle)) {
                    $outputStyle->writeln("Dépose SFTP avec succès");
                }
            }
        }

        return $success;
    }

    /**
     * @param string $mailCode
     * @param array $to
     * @param array $datas
     * @return void
     */
    public function sendMail(string $mailCode, array $to, array $datas): void
    {
        $this->emailSender->send($mailCode, $to, $datas, [], [], [], []);
    }
}
