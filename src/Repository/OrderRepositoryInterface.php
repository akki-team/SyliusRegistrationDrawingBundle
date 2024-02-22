<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Repository;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawingInterface;
use DateTimeInterface;
use Doctrine\ORM\QueryBuilder;

interface OrderRepositoryInterface
{
    public function findAllTransmittedForDrawingExport(RegistrationDrawingInterface $registrationDrawing, DateTimeInterface $dateDebut, DateTimeInterface $dateFin, array $otherTitles): array;

    public function createListByVendorsOrTitlesQueryBuilder(array $vendors, array $titles, array $otherTitles): QueryBuilder;
}
