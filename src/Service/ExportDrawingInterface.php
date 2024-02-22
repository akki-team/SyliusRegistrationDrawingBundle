<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Service;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;

interface ExportDrawingInterface
{
    public function exportDrawing(RegistrationDrawing $registrationDrawing, array $orders, string $filePath, $otherTitles): array;
}
