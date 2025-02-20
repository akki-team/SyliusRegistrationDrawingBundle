<?php

namespace Akki\SyliusRegistrationDrawingBundle\Command;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Akki\SyliusRegistrationDrawingBundle\Repository\OrderRepositoryInterface;
use Akki\SyliusRegistrationDrawingBundle\Service\ExportDrawingInterface;
use Akki\SyliusRegistrationDrawingBundle\Service\ExportServiceInterface;
use Akki\SyliusRegistrationDrawingBundle\Service\GeneratedFileServiceInterface;
use DateTime;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\Payment;
use Sylius\Component\Core\Model\TaxonInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ExportDrawingsCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'export-drawings:generate';

    public function __construct(
        private readonly RepositoryInterface           $registrationDrawingRepository,
        private readonly OrderRepositoryInterface      $orderRepository,
        private readonly ExportDrawingInterface        $exportDrawing,
        private readonly GeneratedFileServiceInterface $generatedFileService,
        private readonly ExportServiceInterface        $exportService,
        private readonly string                        $kernelProjectDir,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Transmission des abonnements/ventes à l\'éditeur partenaire')
            ->setHelp('Cette commande permets de générer un export CSV ou longueur fixe contenant les abonnements et vente d\'un éditeur partenaire')
            ->addArgument('registration_drawing', InputArgument::OPTIONAL, 'Dessin d\'enregistrement')
            ->addArgument('start_date', InputArgument::OPTIONAL, 'Date de début')
            ->addArgument('end_date', InputArgument::OPTIONAL, 'Date de fin')
            ->addArgument('send', InputArgument::OPTIONAL, 'Envoi d\'email et dépose SFTP', 1);
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

            return Command::FAILURE;
        }

        $outputStyle = new SymfonyStyle($input, $output);

        $outputStyle->writeln('Debut génération des commandes éditeurs');

        $inputDrawing = $input->getArgument('registration_drawing');
        $inputStartDate = $input->getArgument('start_date');
        $inputEndDate = $input->getArgument('end_date');
        $inputSend = $input->getArgument('send');

        if ($inputDrawing) {
            $registrationDrawings = $this->registrationDrawingRepository->findByName($inputDrawing);
        } else {
            $registrationDrawings = $this->registrationDrawingRepository->findAll();
        }

        if (!empty($registrationDrawings)) {
            /** @var RegistrationDrawing $drawing */
            foreach ($registrationDrawings as $drawing) {
                if ($inputStartDate && $inputEndDate) {
                    $startDate = (new DateTime($input->getArgument('start_date')))->setTime(0, 0, 0);;
                    $endDate = (new DateTime($input->getArgument('end_date')))->setTime(23, 59, 59);;
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
                        $startDate = new DateTime($day . ' last week midnight');
                        $endDate = new DateTime($day . ' this week midnight -1 sec');
                    }
                }

                $startDateFormated = $startDate->format('Ymd');
                $endDateFormated = $endDate->format('Ymd');

                $otherDrawings = array_filter($this->registrationDrawingRepository->findAll(), function ($dr) use ($drawing) {
                    return $dr !== $drawing;
                });

                $otherTitles = [];

                /** @var RegistrationDrawing $otherDrawing */
                foreach ($otherDrawings as $otherDrawing) {
                    /** @var TaxonInterface $title */
                    foreach ($otherDrawing->getTitles() as $title) {
                        $otherTitles[] = $title;
                    }
                }

                $orders = $this->orderRepository->findAllTransmittedForDrawingExport($drawing, $startDate, $endDate, $otherTitles);
                $this->removeUnSucceededOrderPaymentStatus($orders);

                $fileName = $drawing->getFormat() === Constants::CSV_FORMAT ? "{$drawing->getName()}_{$startDateFormated}_$endDateFormated.csv" : "{$drawing->getName()}_{$startDateFormated}_$endDateFormated.txt";
                $filePath = $this->kernelProjectDir . Constants::DIRECTORY_PUBLIC . Constants::DIRECTORY_EXPORT . $fileName;

                if (!empty($orders)) {
                    $export = $this->exportDrawing->exportDrawing($drawing, $orders, $filePath, $otherTitles);
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
                            $success = $this->exportService->sendSalesReportToVendor($drawing, $filePath, $outputStyle);

                            if ($success) {
                                try {
                                    $this->exportService->sendMail(
                                        Constants::SUCCESS_MAIL_CODE,
                                        explode(';', str_replace(' ', '', $drawing->getRecipients())),
                                        ['fileName' => $fileName, 'totalLines' => $totalLines, 'totalCancelled' => $totalCancellations]
                                    );
                                } catch (\Exception $e) {
                                    $outputStyle->writeln("Erreur pendant l'envoi du mail : " . $e->getMessage());

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

        return Command::SUCCESS;
    }


    private function removeUnSucceededOrderPaymentStatus(array &$orders): void
    {
        $ordersSucceededStatus = [];

        /** @var Order $order */
        foreach ($orders as $order) {
            /** @var Payment $payment */
            foreach ($order->getPayments() as $payment) {
                if ($payment->getMethod()?->getGatewayConfig()?->getFactoryName() !== 'lyra_marketplace') {
                    continue;
                }

                if (!isset($payment->getDetails()['order'])) {
                    continue;
                }

                $orderDetails = json_decode($payment->getDetails()['order'], true);
                if ('SUCCEEDED' === ($orderDetails['status'] ?? '')) {
                    $ordersSucceededStatus = $order;
                }
            }
        }

        $orders = $ordersSucceededStatus;
    }
}
