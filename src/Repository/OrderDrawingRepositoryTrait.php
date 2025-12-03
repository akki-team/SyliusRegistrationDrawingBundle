<?php

namespace Akki\SyliusRegistrationDrawingBundle\Repository;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawingInterface;
use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

trait OrderDrawingRepositoryTrait
{

    public function findAllTransmittedForDrawingExport(RegistrationDrawingInterface $registrationDrawing, DateTimeInterface $dateDebut, DateTimeInterface $dateFin, array $otherTitles): array
    {
        // First query: only payments with state COMPLETED
        $qbCompleted = $this->createListByVendorsOrTitlesQueryBuilder(
            $registrationDrawing->getVendors()->toArray(),
            $registrationDrawing->getTitles()->toArray(),
            $otherTitles
        );

        $completedResults = $qbCompleted
            ->andWhere('o.state != :state_new')
            ->andWhere('o.state != :state_cancelled')
            ->join('o.payments', 'paymentsCompleted', 'WITH', 'paymentsCompleted.state = :paymentStateCompleted')
            ->andWhere('o.checkoutCompletedAt >= :dateDebut')
            ->andWhere('o.checkoutCompletedAt <= :dateFin')
            ->setParameter('state_new', OrderInterface::STATE_NEW)
            ->setParameter('state_cancelled', OrderInterface::STATE_CANCELLED)
            ->setParameter('paymentStateCompleted', PaymentInterface::STATE_COMPLETED)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult();

        // Second query: only payments with state REFUNDED
        $qbRefunded = $this->createListByVendorsOrTitlesQueryBuilder(
            $registrationDrawing->getVendors()->toArray(),
            $registrationDrawing->getTitles()->toArray(),
            $otherTitles
        );

        $refundedResults = $qbRefunded
            ->andWhere('o.state != :state_new')
            ->andWhere('o.state != :state_cancelled')
            ->join('o.payments', 'paymentsRefunded', 'WITH', 'paymentsRefunded.state = :paymentStateRefunded')
            ->andWhere('EXISTS (SELECT cm.id FROM Sylius\\RefundPlugin\\Entity\\CreditMemo cm WHERE cm.order = o.id AND cm.issuedAt >= :dateDebut AND cm.issuedAt <= :dateFin)')
            ->setParameter('state_new', OrderInterface::STATE_NEW)
            ->setParameter('state_cancelled', OrderInterface::STATE_CANCELLED)
            ->setParameter('paymentStateRefunded', PaymentInterface::STATE_REFUNDED)
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult();

        // Merge and deduplicate by order ID
        $merged = array_merge($completedResults, $refundedResults);
        $unique = [];
        foreach ($merged as $order) {
            $unique[$order->getId()] = $order;
        }

        return array_values($unique);
    }

    public function createListByVendorsOrTitlesQueryBuilder(array $vendors, array $titles, array $otherTitles): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.items', 'items')
            ->innerJoin('items.variant', 'variant')
            ->innerJoin('variant.product', 'product')
            ->where('(product.vendor IN (:vendors)) OR (product.vendor IN (:vendors) AND product.mainTaxon NOT IN (:otherTitles)) OR (product.mainTaxon IN (:titles))')
            ->andWhere('o.state != :state')
            ->setParameters([
                'vendors' => $vendors,
                'titles' => $titles,
                'otherTitles' => $otherTitles,
                'state' => OrderInterface::STATE_CART
            ])
            ->distinct('o.id')
            ->groupBy('o.id');
    }
}
