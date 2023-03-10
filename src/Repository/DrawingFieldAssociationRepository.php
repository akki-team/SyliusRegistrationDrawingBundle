<?php

namespace Akki\SyliusRegistrationDrawingBundle\Repository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class DrawingFieldAssociationRepository extends EntityRepository implements DrawingFieldAssociationRepositoryInterface
{
    /**
     * @param int $registrationDrawingId
     * @return array
     */
    public function getFields(int $registrationDrawingId): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.drawingId = :drawingId')
            ->setParameter('drawingId', $registrationDrawingId)
            ->orderBy('f.order', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param int $registrationDrawingId
     * @return array
     */
    public function getFieldsByPosition(int $registrationDrawingId): array
    {
        return $this->createQueryBuilder('f')
            ->where('f.drawingId = :drawingId')
            ->setParameter('drawingId', $registrationDrawingId)
            ->orderBy('f.position', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }
}
