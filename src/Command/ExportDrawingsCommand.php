<?php

namespace Akki\SyliusRegistrationDrawingBundle\Command;

use Akki\SyliusRegistrationDrawingBundle\Controller\RegistrationDrawingController;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use App\Command\KMSendEditorExportCommand;
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
use Symfony\Component\Process\Process;

class ExportDrawingsCommand extends Command
{
    use LockableTrait;

    protected static string $defaultName = 'export-drawings:generate';

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
                    $timestampStartLastMonth = strtotime('first day of last month');
                    $startDate = date('Y-m-d', $timestampStartLastMonth);
                    $timestampEndLastMonth = strtotime('last day of last month');
                    $endDate = date('Y-m-d', $timestampEndLastMonth);

                    $startDateFormated = date('Ymd', $timestampStartLastMonth);
                    $endDateFormated = date('Ymd', $timestampEndLastMonth);
                } else {
                    $timestampStartLastWeek = strtotime($day.' last week');
                    $startDate = date('Y-m-d', $timestampStartLastWeek);
                    $timestampEndLastWeek = (new DateTime())->setTimestamp($timestampStartLastWeek)->modify('+6 days')->getTimestamp();
                    $endDate = date('Y-m-d', $timestampEndLastWeek);

                    $startDateFormated = date('Ymd', $timestampStartLastWeek);
                    $endDateFormated = date('Ymd', $timestampEndLastWeek);
                }
                $dateTimeStart = DateTime::createFromFormat ( 'Ymd', $startDateFormated);
                $dateTimeEnd = DateTime::createFromFormat ( 'Ymd', $endDateFormated);

                $orders = $this->orderRepository->findAllTransmittedForDrawingExport($drawing, $startDate, $endDate);

                $fileName = $drawing->getFormat() === Constants::CSV_FORMAT ? "{$drawing->getName()}_{$startDate}_$endDate.csv" : "{$drawing->getName()}_{$startDate}_$endDate.txt";
                $filePath = $this->kernelProjectDir.self::DIRECTORY_PUBLIC.self::DIRECTORY_EXPORT.$fileName;

                if (!empty($orders)) {
                    $export = $this->registrationDrawingController->exportDrawing($drawing, $orders, $filePath);
                    $totalLines = $export[1];
                    $totalCancellations = $export[2];

                    $drawingFirstVendor = !empty($drawing->getVendors()) ? $drawing->getVendors()->toArray()[0] : null;

                    $this->generatedFileService->addFile($drawingFirstVendor, $fileName, $filePath, $dateTimeStart, $dateTimeEnd, $totalLines, $totalCancellations, $drawing);

                    $this->SendSalesReportToVendor($drawing, $filePath);

                    $this->sendMail($fileName, $output);
                    $outputStyle->newLine();

                    $outputStyle->writeln("fin génération de l'export des commandes du $startDate au $endDate pour le dessin d'enregistrement {$drawing->getName()} déposé ici : $filePath");
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
     * @return void
     */
    public function SendSalesReportToVendor(
        RegistrationDrawing $drawing,
        string $filePath
    ): void
    {
        $rsaSrc = "/home/www-data/.ssh/id_rsa";
        $sendMode = $drawing->getSendMode();
        $depositAddress = $drawing->getDepositAddress();
        $user = $drawing->getUser();
        $password = $drawing->getPassword();
        $host = $drawing->getHost();
        $port = $drawing->getPort();

        // si depose SFTP
        if ($sendMode === 'SSH') {
            $command = "sftp -i $rsaSrc -P $port $user@$host:$depositAddress <<< $'put \"$filePath\"'";

        } else {
            $command = "lftp ftp://$user:$password@$host:$port -e 'put -O \"$depositAddress\" \"$filePath\"; quit'";
        }

        $process = Process::fromShellCommandline($command);
        $process->run() ;
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
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
