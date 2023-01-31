<?php

namespace Akki\SyliusRegistrationDrawingBundle\Repository;

use Doctrine\ORM\QueryBuilder;

interface RegistrationDrawingRepositoryInterface
{
    /**
     * @param array $vendors
     * @param string $dateDebut
     * @param string $dateFin
     * @return array|null
     */
    public function findAllTransmittedForDrawingExport(array $vendors, string $dateDebut, string $dateFin): ?array;

    /**
     * @param array $vendors
     * @return QueryBuilder
     */
    public function createListByVendorsQueryBuilder(array $vendors): QueryBuilder;
}
