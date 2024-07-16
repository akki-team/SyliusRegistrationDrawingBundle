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
        $query = $this->createListByVendorsOrTitlesQueryBuilder($registrationDrawing->getVendors()->toArray(), $registrationDrawing->getTitles()->toArray(), $otherTitles);

        return $query->andWhere('o.state != :state_new')
            ->andWhere('o.state != :state_cancelled')
            ->join('o.payments', 'payments', 'WITH', 'payments.state IN (:paymentStates)')
            ->andWhere('payments.updatedAt >= :dateDebut')
            ->andWhere('payments.updatedAt <= :dateFin')
            ->setParameter('state_new', OrderInterface::STATE_NEW)
            ->setParameter('state_cancelled', OrderInterface::STATE_CANCELLED)
            ->setParameter('paymentStates', [PaymentInterface::STATE_COMPLETED, PaymentInterface::STATE_REFUNDED])
            ->setParameter('dateDebut', $dateDebut)
            ->setParameter('dateFin', $dateFin)
            ->getQuery()
            ->getResult();
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
