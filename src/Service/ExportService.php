<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Service;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawingInterface;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Akki\SyliusRegistrationDrawingBundle\Repository\OrderRepositoryInterface;
use DateTimeInterface;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Process;

final readonly class ExportService implements ExportServiceInterface
{
    public function __construct
    (
        private RepositoryInterface           $registrationDrawingRepository,
        private OrderRepositoryInterface      $orderRepository,
        private ExportDrawingInterface        $exportDrawing,
        private GeneratedFileServiceInterface $generatedFileService,
        private SenderInterface               $emailSender,
        private string                        $kernelProjectDir,
    )
    {
    }

    public function exportDrawing(RegistrationDrawingInterface $registrationDrawing, DateTimeInterface $startDate, DateTimeInterface $endDate, bool $drop = false): void
    {
        /** @var RegistrationDrawingInterface[] $otherDrawings */
        $otherDrawings = array_filter($this->registrationDrawingRepository->findAll(), function ($dr) use ($registrationDrawing) {
            return $dr !== $registrationDrawing;
        });

        $otherTitles = [];

        foreach ($otherDrawings as $otherDrawing) {

            /** @var TaxonInterface $title */
            foreach ($otherDrawing->getTitles() as $title) {
                $otherTitles[] = $title;
            }
        }

        $orders = $this->orderRepository->findAllTransmittedForDrawingExport($registrationDrawing, $startDate, $endDate, $otherTitles);

        $fileName = $registrationDrawing->getFormat() === Constants::CSV_FORMAT ? "{$registrationDrawing->getName()}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}.csv" : "{$registrationDrawing->getName()}_{$startDate->format('Ymd')}_{$endDate->format('Ymd')}.txt";
        $filePath = $this->kernelProjectDir . Constants::DIRECTORY_PUBLIC . Constants::DIRECTORY_EXPORT . $fileName;

        if (!empty($orders)) {
            $export = $this->exportDrawing->exportDrawing($registrationDrawing, $orders, $filePath, $otherTitles);
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

                $drawingFirstVendor = !empty($registrationDrawing->getVendors()) ? $registrationDrawing->getVendors()->toArray()[0] : null;

                $this->generatedFileService->addFile($drawingFirstVendor, $fileName, $filePath, $startDate, $endDate, $totalLines, $totalCancellations, $registrationDrawing);

                if (true === $drop) {
                    $success = $this->sendSalesReportToVendor($registrationDrawing, $filePath);

                    if ($success) {
                        try {
                            $this->sendMail(
                                Constants::SUCCESS_MAIL_CODE,
                                explode(';', str_replace(' ', '', $registrationDrawing->getRecipients())),
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
        } else {
            throw new \Exception('Aucune commande trouvée sur cette période');
        }
    }

    public function sendSalesReportToVendor(RegistrationDrawingInterface $registrationDrawing, string $filePath, SymfonyStyle|null $outputStyle = null): bool
    {
        $success = false;

        $rsaSrc = "~www-data/.ssh/id_rsa";
        $sendMode = $registrationDrawing->getSendMode();
        $depositAddress = $registrationDrawing->getDepositAddress();
        $user = $registrationDrawing->getUser();
        $password = $registrationDrawing->getPassword();
        $host = $registrationDrawing->getHost();
        $port = $registrationDrawing->getPort();

        // si depose SFTP
        if ($sendMode === 'SSH') {
            $command = "echo 'put \"$filePath\"' | sftp  -o StrictHostKeyChecking=no -o HostKeyAlgorithms=ssh-rsa -o PubkeyAcceptedAlgorithms=+ssh-rsa -o UserKnownHostsFile=/dev/null -i $rsaSrc -P $port $user@$host:\"$depositAddress\"";
        } else {
            $lftpOption = "set sftp:connect-program 'ssh -o UserKnownHostsFile=/dev/null -o HostKeyAlgorithms=ssh-rsa -o StrictHostKeyChecking=no'";
            $command = "lftp -c \"$lftpOption; connect sftp://$user:$password@$host:$port;put -O '$depositAddress' '$filePath'\"";
        }

        if (!is_null($outputStyle)) {
            $outputStyle->writeln($command);
        }
        $process = Process::fromShellCommandline($command);

        try {
            $process->run();
        } catch (\Exception $e) {
            if (!is_null($outputStyle)) {
                $outputStyle->writeln("Erreur pendant la depose SFTP : " . $e->getMessage());
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

    public function sendMail(string $mailCode, array $to, array $datas): void
    {
        $this->emailSender->send($mailCode, $to, $datas);
    }
}
