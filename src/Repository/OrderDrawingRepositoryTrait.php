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
    public function findAllTransmittedForDrawingExport(RegistrationDrawing $registrationDrawing, string $dateDebut, string $dateFin): ?array
    {
        $query =  $this->createListByVendorsQueryBuilder($registrationDrawing->getVendors()) ;
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
    public function createListByVendorsQueryBuilder($vendors): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->innerJoin('o.channel', 'channel')
            ->innerJoin('o.items', 'items')
            ->innerJoin('items.variant', 'variant')
            ->innerJoin('variant.product', 'product')
            ->andWhere('product.vendor IN (:vendors)')
            ->andWhere('o.state != :state')
            ->setParameter('vendors', $vendors)
            ->setParameter('state', \Sylius\Component\Core\Model\OrderInterface::STATE_CART)
            ->groupBy('o.id')
        ;
    }
}
