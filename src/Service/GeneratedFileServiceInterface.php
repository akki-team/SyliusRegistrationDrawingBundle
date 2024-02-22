<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Service;

use Akki\SyliusRegistrationDrawingBundle\Entity\GeneratedFileInterface;
use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawingInterface;
use DateTimeInterface;
use Odiseo\SyliusMarketplacePlugin\Entity\VendorInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

interface GeneratedFileServiceInterface
{
    public function addFile(
        VendorInterface|null              $vendor,
        string                            $name,
        string                            $path,
        DateTimeInterface                 $startDate,
        DateTimeInterface                 $endDate,
        int|null                          $totalLines = null,
        int|null                          $totalCancellations = null,
        RegistrationDrawingInterface|null $registrationDrawing = null
    ): void;

    public function readStream(int $id): BinaryFileResponse;

    public function remove(string $path): bool;

    public function fsFilesList(): array;

    public function syncFiles(): void;

    public function findElement(string $name): GeneratedFileInterface|null;
}
