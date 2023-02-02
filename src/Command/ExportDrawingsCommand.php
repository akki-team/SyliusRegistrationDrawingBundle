<?php

namespace Akki\SyliusRegistrationDrawingBundle\Command;

use Akki\SyliusRegistrationDrawingBundle\Controller\RegistrationDrawingController;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use App\Repository\OrderRepositoryInterface;
use App\Service\ExportEditeur\GeneratedFileService;
use DateTime;
use Doctrine\Persistence\ObjectRepository;
use Odiseo\SyliusVendorPlugin\Repository\VendorRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

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
    private const DIRECTORY_EXPORT_SFTP = '/exportsEditeurSynchroFTP/';

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
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('Cette commande est déjà en cours d\'execution. Veuillez attendre la fin de celle-ci');

            return 1;
        }

        $outputStyle = new SymfonyStyle($input, $output);

        $outputStyle->writeln('Debut génération des commandes éditeurs');

        $registrationDrawings = $this->registrationDrawingRepository->findAll() ;

        if (!empty($registrationDrawings)) {
            /** @var RegistrationDrawing $registrationDrawing */
            foreach ($registrationDrawings as $drawing) {
                $periodicity = $drawing->getPeriodicity();

                if ($periodicity === Constants::PERIODICITY_MONTHLY) {
                    $timestampStartLastMonth = strtotime('first day of last month');
                    $startDate = date('Y-m-d', $timestampStartLastMonth);
                    $timestampEndLastMonth = strtotime('last day of last month');
                    $endDate = date('Y-m-d', $timestampEndLastMonth);

                    $startDateFormated = date('Ymd', $timestampStartLastMonth);
                    $endDateFormated = date('Ymd', $timestampEndLastMonth);
                    $dateTimeStart = DateTime::createFromFormat ( 'Ymd', $startDateFormated);
                    $dateTimeEnd = DateTime::createFromFormat ( 'Ymd', $endDateFormated);
                } else {
                    $timestampStartLastWeek = strtotime('monday last week');
                    $startDate = date('Y-m-d', $timestampStartLastWeek);
                    $timestampEndLastWeek = strtotime('sunday last week');
                    $endDate = date('Y-m-d', $timestampEndLastWeek);

                    $startDateFormated = date('Ymd', $timestampStartLastWeek);
                    $endDateFormated = date('Ymd', $timestampEndLastWeek);
                    $dateTimeStart = DateTime::createFromFormat ( 'Ymd', $startDateFormated);
                    $dateTimeEnd = DateTime::createFromFormat ( 'Ymd', $endDateFormated);
                }

                $orders = $this->orderRepository->findAllTransmittedForDrawingExport($drawing, $startDate, $endDate);

                $fileName = $drawing->getFormat() === Constants::CSV_FORMAT ? "{$drawing->getName()}_{$startDate}_{$endDate}.csv" : "{$drawing->getName()}_{$startDate}_{$endDate}.txt";
                $filePath = $this->kernelProjectDir.self::DIRECTORY_PUBLIC.self::DIRECTORY_EXPORT.$fileName;

                if (!empty($orders)) {
                    $export = $this->registrationDrawingController->exportDrawing($drawing, $orders, $filePath);

                    $drawingVendors = $drawing->getVendors()->toArray();

                    $this->generatedFileService->addFile(array_shift($drawingVendors), $fileName, $filePath, $dateTimeStart, $dateTimeEnd);

                    $filePathSynchroSFTPRoot = $this->kernelProjectDir.self::DIRECTORY_PUBLIC.self::DIRECTORY_EXPORT_SFTP;
                    $filePathSynchroSFTPEditor = $filePathSynchroSFTPRoot.$drawing->getId();
                    $fullFilelName = $filePathSynchroSFTPEditor.'/'.$fileName;
                    if (!is_dir($filePathSynchroSFTPEditor)) {
                        if (!mkdir($filePathSynchroSFTPEditor, 0777, true) && !is_dir($filePathSynchroSFTPEditor)) {
                            throw new \RuntimeException(sprintf('Directory "%s" was not created', $filePathSynchroSFTPEditor));
                        }
                    }
                    chmod($filePathSynchroSFTPRoot, 0777);
                    chmod($filePathSynchroSFTPEditor, 0777);
                    file_put_contents($fullFilelName, $export);
                    chmod($fullFilelName, 0777);

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

}
