<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Service;

use Akki\SyliusRegistrationDrawingBundle\Entity\DrawingField;
use Akki\SyliusRegistrationDrawingBundle\Entity\DrawingFieldAssociation;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Akki\SyliusRegistrationDrawingBundle\Helpers\MbHelper;
use Akki\SyliusRegistrationDrawingBundle\Repository\DrawingFieldAssociationRepository;
use Akki\SyliusRegistrationDrawingBundle\Resolver\OrderItemMovementTypeResolverInterface;
use Sylius\Component\Core\Model\Order;
use Sylius\Component\Core\Model\OrderItem;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\Component\Core\OrderPaymentTransitions;
use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\RefundPlugin\Entity\CreditMemoInterface;
use Sylius\RefundPlugin\Repository\CreditMemoRepositoryInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Intl\Countries;

final readonly class ExportDrawing implements ExportDrawingInterface
{
    public function __construct(
        private ExportCsvInterface                $exportCsv,
        private DrawingFieldAssociationRepository $drawingFieldAssociationRepository,
        private RepositoryInterface               $drawingFieldRepository,
        private OrderItemMovementTypeResolverInterface $orderItemMovementTypeResolver,
        private CreditMemoRepositoryInterface     $creditMemoRepository
    )
    {
    }

    public function exportDrawing(RegistrationDrawing $registrationDrawing, array $orders, string $filePath, $otherTitles): array
    {
        $headers = $this->prepareDrawingHeaderToCSVExport($registrationDrawing);
        $registrationDrawingVendors = $registrationDrawing->getVendors()->toArray();
        $registrationDrawingTitles = $registrationDrawing->getTitles()->toArray();

        $fields = [];
        $totalLines = 0;
        $totalCancellations = 0;

        $periodStart = new \DateTime($registrationDrawing->getPeriodicity() === Constants::PERIODICITY_WEEKLY ? Constants::EN_DAYS[$registrationDrawing->getDay()] . ' last week midnight' : 'first day of last month midnight');
        $periodEnd = (clone $periodStart)->modify($registrationDrawing->getPeriodicity() === Constants::PERIODICITY_WEEKLY ? '+7 days' : 'last day of this month')->setTime(23, 59, 59);

        /** @var Order $order */
        foreach ($orders as $order) {
            $items = $order->getItems();

            /** @var OrderItem $item */
            foreach ($items as $item) {
                if (!$this->canExportOrderItem($item, $periodStart, $periodEnd)) {
                    continue;
                }

                $product = $item->getProduct();
                $isValidProduct = false;

                if (in_array($product->getMainTaxon(), $registrationDrawingTitles, true)) {
                    $isValidProduct = true;
                } else {
                    if (!is_null($product->getVendor()) && (in_array($product->getVendor(), $registrationDrawingVendors, true)) && (!in_array($product->getMainTaxon(), $otherTitles, true))) {
                        $isValidProduct = true;
                    }
                }

                if ($isValidProduct) {
                    $data = $this->prepareDrawingfieldsToExport($registrationDrawing, $item);

                    if ($this->orderItemMovementTypeResolver->isOrderItemCanceled($item)) {
                        $totalCancellations++;
                    } else {
                        $totalLines++;
                    }

                    $fields[] = $data;
                }
            }
        }

        if ($registrationDrawing->getFormat() === Constants::CSV_FORMAT) {
            $writer = $this->exportCsv->exportCSV(
                $headers,
                $fields,
                $registrationDrawing->getDelimiter(),
                $registrationDrawing->getEncoding() === Constants::ENCODING_UTF8
            );

            $filesystem = new Filesystem();

            if (false === $filesystem->exists(dirname($filePath))) {
                $filesystem->mkdir(dirname($filePath));
            }

            $fileContent = $writer->getContent();
        } else {
            $fileContent = $this->exportCsv->exportFixedLength($fields,$registrationDrawing->getEncoding() === Constants::ENCODING_UTF8);
        }

        if ($registrationDrawing->getEncoding() === Constants::ENCODING_ANSI) {
            $fileContent = iconv("UTF-8", "Windows-1252", $fileContent);
        }

        file_put_contents($filePath, $fileContent);

        return [$fileContent, $totalLines, $totalCancellations];
    }

    private function prepareDrawingfieldsToExport(RegistrationDrawing $registrationDrawing, OrderItem $orderItem): array
    {
        $fieldAssociations = $this->getDrawingRegistrationFields($registrationDrawing);
        $datas = [];

        /** @var DrawingFieldAssociation $fieldAssociation */
        foreach ($fieldAssociations as $fieldAssociation) {
            /** @var DrawingField $field */
            $field = $this->drawingFieldRepository->find($fieldAssociation->getFieldId());
            $listAccessors = $field->getEquivalent();
            $data = null;

            if (!is_null($listAccessors)) {
                $accessors = explode('/', $listAccessors);

                $data = $orderItem;

                foreach ($accessors as $accessor) {
                    $data = $this->getAccessor($accessor, $data);

                    if (is_null($data)) {
                        break;
                    }
                }
            }

            if (!is_null($data)) {
                // Formats dateTime
                if ($data instanceof \DateTime) {
                    if (!empty($fieldAssociation->getFormat())) {
                        $data = $data->format($fieldAssociation->getFormat());
                    } else {
                        $data = $data->format('dmY');
                    }
                }

                // Gestion des champs booléens
                if (gettype($data) === "boolean") {
                    $data = $data ? '1' : '0';
                }
            } else {
                // Gestion des champs de flux de retour
                if (in_array($field->getName(), Constants::RETURN_FLOW_FIELDS)) {
                    if ($registrationDrawing->getFormat() === Constants::FIXED_LENGTH_FORMAT) {
                        if (!empty($fieldAssociation->getLength())) {
                            $data = $this->applyPad('', $fieldAssociation->getLength());
                        } else {
                            $data = '';
                        }
                    }
                }

                // Gestion des champs avec data à construire
                if ($field->getName() === Constants::OFFER_TYPE_FIELD) {
                    $data = $orderItem->getProduct()->isOffreADL() ? Constants::ADL_OFFER_TYPE : Constants::ADD_OFFER_TYPE;
                }

                if ($field->getName() === Constants::DATE_TRANSMISSION_FIELD) {
                    $data = (new \DateTime('now'))->format('dmY');
                }

                if ($field->getName() === Constants::BILLING_COUNTRY_FIELD) {
                    $data = Countries::getName($orderItem->getOrder()->getBillingAddress()->getCountryCode());
                }

                if ($field->getName() === Constants::SHIPPING_COUNTRY_FIELD) {
                    $data = Countries::getName($orderItem->getShippingAddress()->getCountryCode());
                }
            }

            // Gestion du champ "Type mouvement" si paiement remboursé
            if (($field->getName() === Constants::MOVEMENT_TYPE_FIELD) && $this->orderItemMovementTypeResolver->isOrderItemCanceled($orderItem)) {
                $data = OrderPaymentTransitions::TRANSITION_REFUND;
            }

            // Gestion du champ "Id achat KM" en longueur fixe => On ne prend que les 6 derniers numéros
            if (($field->getName() === Constants::KM_PURCHASE_ID_FIELD) && ($registrationDrawing->getFormat() === Constants::FIXED_LENGTH_FORMAT)) {
                $data = substr((string)$data, -6);
            }

            // Champ avec prix à formatter
            if (($field->getName() === Constants::OFFER_AMOUNT_FIELD) && ($registrationDrawing->getCurrencyFormat() === Constants::CURRENCY_NUMBER_FORMAT)) {
                $data = number_format((int)$data / 100, 2, $registrationDrawing->getCurrencyDelimiter(), '');
            }

            // Selection
            if (!empty($fieldAssociation->getSelection())) {
                $data = $this->substitute($data, $fieldAssociation->getSelection());
            }

            if ($registrationDrawing->getFormat() === Constants::FIXED_LENGTH_FORMAT) {
                if (!empty($fieldAssociation->getLength())) {
                    $data = $this->applyPad($data, $fieldAssociation->getLength());
                }
            }

            $datas[] = is_null($data) ? '' : $data;
        }

        return $datas;
    }

    private function prepareDrawingHeaderToCSVExport(RegistrationDrawing $registrationDrawing): array
    {
        $header = [];

        $fields = $this->getDrawingRegistrationFields($registrationDrawing);

        /** @var DrawingFieldAssociation $field */
        foreach ($fields as $field) {
            $header[] = $field->getName();
        }

        return $header;
    }

    private function getDrawingRegistrationFields(RegistrationDrawing $registrationDrawing): array
    {
        return $this->drawingFieldAssociationRepository->getFields($registrationDrawing->getId());
    }

    private function getAccessor($accessor, $data)
    {
        $getter = 'get' . $accessor;
        if (method_exists($data, $getter)) {
            return call_user_func_array([$data, $getter], []);
        }

        $getter = 'is' . $accessor;
        if (method_exists($data, $getter)) {
            return call_user_func_array([$data, $getter], []);
        }

        return false;
    }

    private function substitute($data, string $selections): ?string
    {
        $returnedData = $data;
        $couples = explode(';', $selections);

        foreach ($couples as $couple) {
            $coupleValues = explode('=>', $couple);
            if (count($coupleValues) > 1) {
                $keyCouple = trim($coupleValues[0]);
                $valueCouple = trim($coupleValues[1]);

                if ($keyCouple === $data) {
                    $returnedData = $valueCouple;
                    break;
                }
            }
        }

        return $returnedData;
    }

    private function applyPad($value, $zone): string
    {
        $value = trim((string)$value);
        $length = mb_strlen($value);

        if ($length > $zone) {
            $value = mb_substr($value, 0, $zone);
        }

        if ($length < $zone) {
            $value = MbHelper::mb_str_pad($value, $zone);
        }

        return $value;
    }

    private function canExportOrderItem(OrderItemInterface $orderItem, \DateTimeInterface $startDate, \DateTimeInterface $endDate): bool
    {
        $paidInPeriod = $this->isDateInPeriod($orderItem->getOrder()->getCheckoutCompletedAt() ?? $orderItem->getOrder()->getCreatedAt(), $startDate, $endDate);
        $refundedInPeriod = $this->hasItemBeenRefundedInPeriod($orderItem, $startDate, $endDate);

        // Do not include item in export if it was paid and refunded in the export period
        return !($paidInPeriod && $refundedInPeriod);
    }

    private function getCreditMemosForOrderItem(OrderItemInterface $orderItem): array
    {
        $creditMemos = $this->creditMemoRepository->findByOrderId((string)$orderItem->getOrder()->getId());
        if (count($creditMemos) === 0) {
            return [];
        }

        return array_filter($creditMemos, function (CreditMemoInterface $creditMemo) use ($orderItem) {
            foreach ($creditMemo->getLineItems() as $creditMemoItem) {
                if ($creditMemoItem->getProduct()->getId() === $orderItem->getProduct()->getId()) {
                    return true;
                }
            }

            return false;
        });
    }

    private function hasItemBeenRefundedInPeriod(OrderItemInterface $orderItem, \DateTimeInterface $dateDebut, \DateTimeInterface $dateFin): bool
    {
        if (!$this->orderItemMovementTypeResolver->isOrderItemCanceled($orderItem)) {
            return false;
        }

        $creditMemos = $this->getCreditMemosForOrderItem($orderItem);
        foreach ($creditMemos as $creditMemo) {
            if ($this->isDateInPeriod($creditMemo->getIssuedAt(), $dateDebut, $dateFin)) {
                return true;
            }
        }

        return false;
    }

    private function isDateInPeriod(\DateTimeInterface $date, \DateTimeInterface $dateStart, \DateTimeInterface $dateEnd): bool
    {
        return $date >= $dateStart && $date <= $dateEnd;
    }
}
