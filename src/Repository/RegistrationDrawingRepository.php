<?php

namespace Akki\SyliusRegistrationDrawingBundle\Repository;

use App\Entity\Order\Order;
use Doctrine\ORM\QueryBuilder;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Payment\Model\PaymentInterface;

class RegistrationDrawingRepository extends EntityRepository implements RegistrationDrawingRepositoryInterface
{
    /**
     * @param array $vendors
     * @param string $dateDebut
     * @param string $dateFin
     * @return array|null
     */
    public function findAllTransmittedForDrawingExport(array $vendors, string $dateDebut, string $dateFin): ?array
    {
        $query =  $this->createListByVendorsQueryBuilder($vendors);

        $query->andWhere('o.state != :state_new')
            ->andWhere('o.state != :state_cancelled')
            ->setParameter('state_new', OrderInterface::STATE_NEW)
            ->setParameter('state_cancelled', OrderInterface::STATE_CANCELLED)

            ->join('o.payments', 'payments', 'WITH', 'payments.state = :paymentState')
            ->setParameter('paymentState', PaymentInterface::STATE_COMPLETED)
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
     * @param array $vendors
     * @return QueryBuilder
     */
    public function createListByVendorsQueryBuilder(array $vendors): QueryBuilder
    {
        return $this->createQueryBuilder('o')
            ->from(Order::class, 'o')
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
