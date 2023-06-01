<?php

namespace Akki\SyliusRegistrationDrawingBundle\Command;

use Akki\SyliusRegistrationDrawingBundle\Controller\RegistrationDrawingController;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use App\Command\KMSendEditorExportCommand;
use App\Command\KMSendEditorExportErrorCommand;
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
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
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
            ->addArgument('registration_drawing', InputArgument::OPTIONAL, 'Dessin d\'enregistrement')
            ->addArgument('start_date', InputArgument::OPTIONAL, 'Date de début')
            ->addArgument('end_date', InputArgument::OPTIONAL, 'Date de fin')
            ->addArgument('send', InputArgument::OPTIONAL, 'Envoi d\'email et dépose SFTP', 1)
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

        $inputDrawing = $input->getArgument('registration_drawing');
        $inputStartDate = $input->getArgument('start_date');
        $inputEndDate = $input->getArgument('end_date');
        $inputSend = $input->getArgument('send');

        if ($inputDrawing) {
            $registrationDrawings = $this->registrationDrawingRepository->findByName($inputDrawing);
        }
        else {
            $registrationDrawings = $this->registrationDrawingRepository->findAll();
        }

        if (!empty($registrationDrawings)) {
            /** @var RegistrationDrawing $registrationDrawing */
            foreach ($registrationDrawings as $drawing) {
                if ($inputStartDate && $inputEndDate) {
                    $startDate = (new DateTime($input->getArgument('start_date')))->setTime(0,0, 0);;
                    $endDate = (new DateTime($input->getArgument('end_date')))->setTime(23,59, 59);;
                } else {
                    $periodicity = $drawing->getPeriodicity();
                    $day = Constants::EN_DAYS[$drawing->getDay()];

                    if ($periodicity === Constants::PERIODICITY_WEEKLY && $day !== strtolower(date('l'))) {
                        continue;
                    }

                    if ($periodicity === Constants::PERIODICITY_MONTHLY && date('j') !== '1') {
                        continue;
                    }

                    if ($periodicity === Constants::PERIODICITY_MONTHLY) {
                        $startDate = new DateTime('first day of last month midnight');
                        $endDate = new DateTime('first day of this month midnight -1 sec');
                    } else {
                        $startDate = new DateTime($day.' last week midnight');
                        $endDate = new DateTime($day.' this week midnight -1 sec');
                    }
                }

                $startDateFormated = $startDate->format('Y-m-d');
                $endDateFormated = $endDate->format('Y-m-d');

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

                $fileName = $drawing->getFormat() === Constants::CSV_FORMAT ? "{$drawing->getName()}_{$startDateFormated}_$endDateFormated.csv" : "{$drawing->getName()}_{$startDateFormated}_$endDateFormated.txt";
                $filePath = $this->kernelProjectDir.self::DIRECTORY_PUBLIC.self::DIRECTORY_EXPORT.$fileName;

                if (!empty($orders)) {
                    $export = $this->registrationDrawingController->exportDrawing($drawing, $orders, $filePath, $otherTitles);
                    $totalLines = $export[1];
                    $totalCancellations = $export[2];

                    if ($export[1] > 0 || $export[2] > 0) {
                        if (null === $export[0]) {
                            $outputStyle->writeln("Erreur pendant la génération du fichier");
                            $this->sendErrorMail($fileName, 'Erreur pendant la génération du fichier', $output);
                        }

                        $drawingFirstVendor = !empty($drawing->getVendors()) ? $drawing->getVendors()->toArray()[0] : null;

                        $this->generatedFileService->addFile($drawingFirstVendor, $fileName, $filePath, $startDate, $endDate, $totalLines, $totalCancellations, $drawing);

                        if ($inputSend === 1) {
                            $success = $this->sendSalesReportToVendor($drawing, $filePath, $outputStyle, $output);

                            if ($success) {
                                try {
                                    $this->sendMail($fileName, $output);
                                } catch (\Exception $e) {
                                    $outputStyle->writeln("Erreur pendant l'envoi du mail : ".$e->getMessage());
                                    $this->sendErrorMail($fileName, $e->getMessage(), $output);
                                }

                                $outputStyle->newLine();
                            }
                        }

                        $outputStyle->writeln("fin génération de l'export des commandes du $startDateFormated au $endDateFormated pour le dessin d'enregistrement {$drawing->getName()} déposé ici : $filePath");
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
     * @param OutputInterface $output
     * @return bool
     */
    public function sendSalesReportToVendor(
        RegistrationDrawing $drawing,
        string $filePath,
        SymfonyStyle $outputStyle,
        OutputInterface $output
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

        $outputStyle->writeln($command);
        $process = Process::fromShellCommandline($command);

        try {
            $process->run() ;
        } catch (\Exception $e) {
            $outputStyle->writeln("Erreur pendant la depose SFTP : ".$e->getMessage());
            $this->sendErrorMail(basename($filePath), $e->getMessage(), $output);
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

    /**
     * @param string $fileName
     * @param string $error
     * @param OutputInterface $output
     * @return void
     */
    private function sendErrorMail(string $fileName, string $error, OutputInterface $output): void
    {
        $sendEditorExportCommand = KMSendEditorExportErrorCommand::getDefaultName();
        $command = $this->getApplication()->find($sendEditorExportCommand);
        $sendEditorExportCommandInput = new ArrayInput(['fileName' => $fileName, 'error' => $error]);
        $command->run($sendEditorExportCommandInput, $output);
    }

}
