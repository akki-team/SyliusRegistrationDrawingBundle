<?php

namespace Akki\SyliusRegistrationDrawingBundle\Command;

use Akki\SyliusRegistrationDrawingBundle\Controller\RegistrationDrawingController;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use App\Entity\Taxonomy\Taxon;
use App\Repository\OrderRepositoryInterface;
use App\Service\ExportEditeur\GeneratedFileService;
use DateTime;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

class ExportDrawingsFromPeriodCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'export-drawings:generate-period';

    /** @var ObjectRepository $registrationDrawingRepository */
    protected ObjectRepository $registrationDrawingRepository;

    /** @var OrderRepositoryInterface $orderRepository */
    protected OrderRepositoryInterface $orderRepository;

    /** @var RegistrationDrawingController $registrationDrawingController */
    protected RegistrationDrawingController $registrationDrawingController ;

    /** @var string $kernelProjectDir */
    protected string $kernelProjectDir;

    private const DIRECTORY_PUBLIC = '/var';
    private const DIRECTORY_EXPORT = '/exportsEditeur/';

    /**
     * @param ObjectRepository $registrationDrawingRepository
     * @param OrderRepositoryInterface $orderRepository
     * @param RegistrationDrawingController $registrationDrawingController
     * @param GeneratedFileService $generatedFileService
     * @param KernelInterface $kernel
     */
    public function __construct(
        ObjectRepository $registrationDrawingRepository,
        OrderRepositoryInterface $orderRepository,
        RegistrationDrawingController $registrationDrawingController,
        GeneratedFileService $generatedFileService,
        KernelInterface $kernel
    )
    {
        parent::__construct();
        $this->registrationDrawingRepository = $registrationDrawingRepository;
        $this->orderRepository = $orderRepository;
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
            ->setDescription('Transmission des abonnements/ventes à l\'éditeur partenaire pour une période')
            ->setHelp('Cette commande permets de générer un export CSV ou longueur fixe contenant les abonnements et vente d\'un éditeur partenaire pour une période')
            ->addArgument('date_debut', InputArgument::REQUIRED, 'Date de début')
            ->addArgument('date_fin', InputArgument::REQUIRED, 'Date de fin')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws \Exception
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
                $startDate = (new DateTime($input->getArgument('date_debut')))->setTime(0,0, 0);;
                $endDate = (new DateTime($input->getArgument('date_fin')))->setTime(23,59, 59);;

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
                        $drawingFirstVendor = !empty($drawing->getVendors()) ? $drawing->getVendors()->toArray()[0] : null;

                        $this->generatedFileService->addFile($drawingFirstVendor, $fileName, $filePath, $startDate, $endDate, $totalLines, $totalCancellations, $drawing);

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
