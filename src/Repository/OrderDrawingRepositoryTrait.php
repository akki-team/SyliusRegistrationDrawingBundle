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
     * @param string $dateDebut
     * @param string $dateFin
     * @return array|null
     */
    public function findAllTransmittedForDrawingExport(RegistrationDrawing $registrationDrawing, string $dateDebut, string $dateFin, array $otherTitles): ?array
    {
        $query =  $this->createListByVendorsOrTitlesQueryBuilder($registrationDrawing->getVendors(), $registrationDrawing->getTitles(), $otherTitles) ;
        $query->andWhere('o.state != :state_new')
            ->andWhere('o.state != :state_cancelled')
            ->setParameter('state_new', OrderInterface::STATE_NEW)
            ->setParameter('state_cancelled', OrderInterface::STATE_CANCELLED)

            ->join('o.payments', 'payments', 'WITH', 'payments.state IN (:paymentStates)')
            ->setParameter('paymentStates', [PaymentInterface::STATE_COMPLETED, PaymentInterface::STATE_REFUNDED])
        ;

        if ($dateDebut !== '') {
            $dateDebut .= ' 00:00:00';
            $query
                ->andWhere(" payments.updatedAt >= STR_TO_DATE(:dateDebut,'%Y-%m-%d %H:%i:%s')")
                ->setParameter('dateDebut', $dateDebut);
        }

        if ($dateFin !== '') {
            $dateFin .= ' 23:59:59';
            $query
                ->andWhere(" payments.updatedAt <= STR_TO_DATE(:dateFin,'%Y-%m-%d %H:%i:%s')")
                ->setParameter('dateFin', $dateFin);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @param $vendors
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
