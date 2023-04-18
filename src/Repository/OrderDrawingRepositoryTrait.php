<?php

namespace Akki\SyliusRegistrationDrawingBundle\Repository;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Doctrine\ORM\QueryBuilder;
use Sylius\Component\Core\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

trait OrderDrawingRepositoryTrait
{
    /**
     * @param RegistrationDrawing $registrationDrawing
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @param array $otherTitles
     * @return array|null
     */
    public function findAllTransmittedForDrawingExport(RegistrationDrawing $registrationDrawing, \DateTime $dateDebut, \DateTime $dateFin, array $otherTitles): ?array
    {
        $query =  $this->createListByVendorsOrTitlesQueryBuilder($registrationDrawing->getVendors(), $registrationDrawing->getTitles(), $otherTitles) ;

        return $query->andWhere('o.state != :state_new')
            ->andWhere('o.state != :state_cancelled')
            ->join('o.payments', 'payments', 'WITH', 'payments.state IN (:paymentStates)')
            ->andWhere('payments.updatedAt >= :dateDebut')
            ->andWhere('payments.updatedAt <= :dateFin')
            ->setParameters([
                'state_new' => OrderInterface::STATE_NEW,
                'state_cancelled' => OrderInterface::STATE_CANCELLED,
                'paymentStates' => [PaymentInterface::STATE_COMPLETED, PaymentInterface::STATE_REFUNDED],
                'dateDebut' => $dateDebut,
                'dateFin' => $dateFin
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param $vendors
     * @param $titles
     * @param $otherTitles
     * @return QueryBuilder
     */
    public function createListByVendorsOrTitlesQueryBuilder($vendors, $titles, $otherTitles): QueryBuilder
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
            ->groupBy('o.id')
        ;
    }
}
