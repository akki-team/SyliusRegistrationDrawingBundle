<?php

namespace Akki\SyliusRegistrationDrawingBundle\Repository;

interface DrawingFieldAssociationRepositoryInterface
{
    /**
     * @param int $registrationDrawingId
     * @return array
     */
    public function getFields(int $registrationDrawingId): array;
}
