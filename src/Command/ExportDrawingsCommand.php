<?php

namespace Akki\SyliusRegistrationDrawingBundle\Command;

use Akki\SyliusRegistrationDrawingBundle\Controller\RegistrationDrawingController;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use App\Command\KMSendEditorExportCommand;
use App\Entity\Taxonomy\Taxon;
use App\Repository\OrderRepositoryInterface;
use App\Service\ExportEditeur\GeneratedFileService;
use DateTime;
use Doctrine\Persistence\ObjectRepository;
use Odiseo\SyliusVendorPlugin\Repository\VendorRepositoryInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

class ExportDrawingsCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'export-drawings:generate';

    /** @var ObjectRepository $registrationDrawingRepository */
    protected ObjectRepository $registrationDrawingRepository;

    /** @var OrderRepositoryInterface $orderRepository */
    protected OrderRepositoryInterface $orderRepository;

    /** @var VendorRepositoryInterface $vendorRepository */
    protected VendorRepositoryInterface $vendorRepository ;

    /** @var RegistrationDrawingController $registrationDrawingController */
    protected RegistrationDrawingController $registrationDrawingController ;

    /** @var GeneratedFileService $generatedFileService */
    private GeneratedFileService $generatedFileService;

    /** @var string $kernelProjectDir */
    protected string $kernelProjectDir;

    private const DIRECTORY_PUBLIC = '/var';
    private const DIRECTORY_EXPORT = '/exportsEditeur/';

    private const EN_DAYS = [
        'LUNDI' => 'monday',
        'MARDI' => 'tuesday',
        'MERCREDI' => 'wednesday',
        'JEUDI' => 'thursday',
        'VENDREDI' => 'friday',
        'SAMEDI' => 'saturday',
        'DIMANCHE' => 'sunday'
    ];

    /**
     * @param ObjectRepository $registrationDrawingRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param VendorRepositoryInterface $vendorRepository
     * @param RegistrationDrawingController $registrationDrawingController
     * @param GeneratedFileService $generatedFileService
     * @param KernelInterface $kernel
     */
    public function __construct(
        ObjectRepository $registrationDrawingRepository,
        OrderRepositoryInterface $orderRepository,
        VendorRepositoryInterface $vendorRepository,
        RegistrationDrawingController $registrationDrawingController,
        GeneratedFileService $generatedFileService,
        KernelInterface $kernel
    )
    {
        parent::__construct();
        $this->registrationDrawingRepository = $registrationDrawingRepository;
        $this->orderRepository = $orderRepository;
        $this->vendorRepository = $vendorRepository;
        $this->registrationDrawingController = $registrationDrawingController;
        $this->generatedFileService = $generatedFileService;
        $this->kernelProjectDir = $kernel->getProjectDir();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Transmission des abonnements/ventes à l\'éditeur partenaire')
            ->setHelp('Cette commande permets de générer un export CSV ou longueur fixe contenant les abonnements et vente d\'un éditeur partenaire')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('Cette commande est déjà en cours d\'execution. Veuillez attendre la fin de celle-ci');

            return 1;
        }

        $outputStyle = new SymfonyStyle($input, $output);

        $outputStyle->writeln('Debut génération des commandes éditeurs');

        $registrationDrawings = $this->registrationDrawingRepository->findAll();

        if (!empty($registrationDrawings)) {
            /** @var RegistrationDrawing $registrationDrawing */
            foreach ($registrationDrawings as $drawing) {
                $periodicity = $drawing->getPeriodicity();
                $day = self::EN_DAYS[$drawing->getDay()];

                if ($periodicity === Constants::PERIODICITY_WEEKLY && $day !== strtolower(date('l'))) {
                    continue;
                }

                if ($periodicity === Constants::PERIODICITY_MONTHLY && date('j') !== '1') {
                    continue;
                }

                if ($periodicity === Constants::PERIODICITY_MONTHLY) {
                    $timestampStartLastMonth = strtotime('first day of last month midnight');
                    $startDate = date('Y-m-d', $timestampStartLastMonth);
                    $timestampEndLastMonth = strtotime('first day of this month midnight -1 sec');
                    $endDate = date('Y-m-d', $timestampEndLastMonth);

                    $startDateFormated = date('Ymd', $timestampStartLastMonth);
                    $endDateFormated = date('Ymd', $timestampEndLastMonth);
                } else {
                    $timestampStartLastWeek = strtotime($day.' last week midnight');
                    $startDate = date('Y-m-d', $timestampStartLastWeek);
                    $timestampEndLastWeek = strtotime($day.' this week midnight -1 sec');
                    $endDate = date('Y-m-d', $timestampEndLastWeek);

                    $startDateFormated = date('Ymd', $timestampStartLastWeek);
                    $endDateFormated = date('Ymd', $timestampEndLastWeek);
                }
                $dateTimeStart = DateTime::createFromFormat ( 'Ymd', $startDateFormated);
                $dateTimeEnd = DateTime::createFromFormat ( 'Ymd', $endDateFormated);

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

                $fileName = $drawing->getFormat() === Constants::CSV_FORMAT ? "{$drawing->getName()}_{$startDate}_$endDate.csv" : "{$drawing->getName()}_{$startDate}_$endDate.txt";
                $filePath = $this->kernelProjectDir.self::DIRECTORY_PUBLIC.self::DIRECTORY_EXPORT.$fileName;

                if (!empty($orders)) {
                    $export = $this->registrationDrawingController->exportDrawing($drawing, $orders, $filePath, $otherTitles);
                    $totalLines = $export[1];
                    $totalCancellations = $export[2];

                    if ($export[1] > 0) {
                        $drawingFirstVendor = !empty($drawing->getVendors()) ? $drawing->getVendors()->toArray()[0] : null;

                    $this->generatedFileService->addFile($drawingFirstVendor, $fileName, $filePath, $dateTimeStart, $dateTimeEnd, $totalLines, $totalCancellations, $drawing);

                    $success = $this->sendSalesReportToVendor($drawing, $filePath, $outputStyle);

                    if ($success) {
                        $this->sendMail($fileName, $output);
                        $outputStyle->newLine();
                    }

                        $outputStyle->writeln("fin génération de l'export des commandes du $startDate au $endDate pour le dessin d'enregistrement {$drawing->getName()} déposé ici : $filePath");
                    }
                }
            }
        } else {
            $outputStyle->writeln('Aucun éditeur trouvé.');
        }

        $outputStyle->writeln('Fin génération des commandes éditeurs.');
        $this->release();

        return 0;
    }

    /**
     * @param RegistrationDrawing $drawing
     * @param string $filePath
     * @param SymfonyStyle $outputStyle
     * @return bool
     */
    public function sendSalesReportToVendor(
        RegistrationDrawing $drawing,
        string $filePath,
        SymfonyStyle $outputStyle
    ): bool
    {
        $success = false;

        $rsaSrc = "/home/www-data/.ssh/id_rsa";
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

        $outputStyle->writeln($command);
        $process = Process::fromShellCommandline($command);

        try {
            $process->run() ;
        } catch (ProcessTimedOutException $pte) {
            $outputStyle->writeln("Erreur pendant la depose SFTP : ".$pte->getMessage());
        } finally {
            if (!$process->isSuccessful()) {
                $outputStyle->writeln("Erreur pendant la depose SFTP");
            } else {
                $success = true;
                $outputStyle->writeln("Dépose SFTP avec succès");
            }
        }

        return $success;
    }

    /**
     * @param string $fileName
     * @param OutputInterface $output
     * @return void
     */
    private function sendMail(string $fileName, OutputInterface $output): void
    {
        $sendEditorExportCommand = KMSendEditorExportCommand::getDefaultName();
        $command = $this->getApplication()->find($sendEditorExportCommand);
        $sendEditorExportCommandInput = new ArrayInput(['fileName' => $fileName]);
        $command->run($sendEditorExportCommandInput, $output);
    }

}
