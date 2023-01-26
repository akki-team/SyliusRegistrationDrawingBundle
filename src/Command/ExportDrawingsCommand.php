<?php

namespace Akki\SyliusRegistrationDrawingBundle\Command;

use Akki\SyliusRegistrationDrawingBundle\Controller\RegistrationDrawingController;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use App\Entity\Vendor\Vendor;
use App\Repository\OrderRepositoryInterface;
use Exception;
use Odiseo\SyliusVendorPlugin\Repository\VendorRepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportDrawingsCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'export-drawings:generate';

    protected OrderRepositoryInterface $orderRepository ;

    protected VendorRepositoryInterface $vendorRepository ;

    protected RegistrationDrawingController $registrationDrawingController ;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param VendorRepositoryInterface $vendorRepository
     * @param RegistrationDrawingController $registrationDrawingController
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        VendorRepositoryInterface $vendorRepository,
        RegistrationDrawingController $registrationDrawingController
    )
    {
        parent::__construct();
        $this->orderRepository = $orderRepository;
        $this->vendorRepository = $vendorRepository;
        $this->registrationDrawingController = $registrationDrawingController;
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
     * {@inheritdoc}
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$this->lock()) {
            $output->writeln('Cette commande est déjà en cours d\'execution. Veuillez attendre la fin de celle-ci');

            return 1;
        }

        $outputStyle = new SymfonyStyle($input, $output);

        $outputStyle->writeln('Debut génération des commandes éditeurs');

        $vendors = $this->vendorRepository->findAll() ;

        if (!empty($vendors)) {
            /** @var Vendor $vendor */
            foreach ($vendors as $vendor) {
                $periodicity = $vendor->getRegistrationDrawing()->getPeriodicity();

                if ($periodicity === Constants::PERIODICITY_MONTHLY) {
                    $timestampStartLastMonth = strtotime('last day of last month');
                    $startDate = date('Y-m-d', $timestampStartLastMonth);
                    $timestampEndLastMonth = strtotime('last day of last month');
                    $endDate = date('Y-m-d', $timestampEndLastMonth);
                } else {
                    $timestampStartLastWeek = strtotime('monday last week');
                    $startDate = date('Y-m-d', $timestampStartLastWeek);
                    $timestampEndLastWeek = strtotime('sunday last week');
                    $endDate = date('Y-m-d', $timestampEndLastWeek);
                }

                $orders = $this->orderRepository->findAllTransmittedForExportVendor($vendor, $startDate, $endDate);

                if (!empty($orders)) {
                    $this->registrationDrawingController->exportDrawing($vendor, $orders);
                }
            }
        }else{
            $outputStyle->writeln('Aucun éditeur trouvé.');
        }

        $outputStyle->writeln('Fin génération des commandes éditeurs.');
        $this->release();

        return 0;
    }

}
