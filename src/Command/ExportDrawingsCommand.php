<?php

namespace Akki\SyliusRegistrationDrawingBundle\Command;

use Akki\SyliusRegistrationDrawingBundle\Controller\RegistrationDrawingController;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Akki\SyliusRegistrationDrawingBundle\Service\ExportService;
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

    /** @var ExportService $exportService */
    private ExportService $exportService;

    /**
     * @param ObjectRepository $registrationDrawingRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param VendorRepositoryInterface $vendorRepository
     * @param RegistrationDrawingController $registrationDrawingController
     * @param GeneratedFileService $generatedFileService
     * @param KernelInterface $kernel
     * @param KMSenderInterface $emailSender
     * @param ExportService $exportService
     */
    public function __construct(
        ObjectRepository $registrationDrawingRepository,
        OrderRepositoryInterface $orderRepository,
        VendorRepositoryInterface $vendorRepository,
        RegistrationDrawingController $registrationDrawingController,
        GeneratedFileService $generatedFileService,
        KernelInterface $kernel,
        KMSenderInterface $emailSender,
        ExportService $exportService
    )
    {
        parent::__construct();
        $this->registrationDrawingRepository = $registrationDrawingRepository;
        $this->orderRepository = $orderRepository;
        $this->vendorRepository = $vendorRepository;
        $this->registrationDrawingController = $registrationDrawingController;
        $this->generatedFileService = $generatedFileService;
        $this->kernelProjectDir = $kernel->getProjectDir();
        $this->emailSender = $emailSender;
        $this->exportService = $exportService;
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
            /** @var RegistrationDrawing $drawing */
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
                $filePath = $this->kernelProjectDir.Constants::DIRECTORY_PUBLIC.Constants::DIRECTORY_EXPORT.$fileName;

                if (!empty($orders)) {
                    $export = $this->registrationDrawingController->exportDrawing($drawing, $orders, $filePath, $otherTitles);
                    $totalLines = $export[1];
                    $totalCancellations = $export[2];

                    if ($export[1] > 0 || $export[2] > 0) {
                        if (null === $export[0]) {
                            $outputStyle->writeln("Erreur pendant la génération du fichier");

                            $this->exportService->sendMail(
                                Constants::ERROR_MAIL_CODE,
                                Constants::ERROR_MAIL_RECIPIENTS,
                                ['fileName' => $fileName, 'error' => 'Erreur pendant la génération du fichier']
                            );
                        }

                        $drawingFirstVendor = !empty($drawing->getVendors()) ? $drawing->getVendors()->toArray()[0] : null;

                        $this->generatedFileService->addFile($drawingFirstVendor, $fileName, $filePath, $startDate, $endDate, $totalLines, $totalCancellations, $drawing);

                        if ($inputSend === 1) {
                            $success = $this->exportService->sendSalesReportToVendor($drawing, $filePath, $outputStyle, $output);

                            if ($success) {
                                try {
                                    $this->exportService->sendMail(
                                        Constants::SUCCESS_MAIL_CODE,
                                        explode(';', $drawing->getRecipients()),
                                        ['fileName' => $fileName, 'totalLines' => $totalLines, 'totalCancelled' => $totalCancellations]
                                    );
                                } catch (\Exception $e) {
                                    $outputStyle->writeln("Erreur pendant l'envoi du mail : ".$e->getMessage());

                                    $this->exportService->sendMail(
                                        Constants::ERROR_MAIL_CODE,
                                        Constants::ERROR_MAIL_RECIPIENTS,
                                        ['fileName' => $fileName, 'error' => $e->getMessage()]
                                    );
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

}
