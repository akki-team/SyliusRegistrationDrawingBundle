<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use DateTimeInterface;
use Odiseo\SyliusMarketplacePlugin\Entity\VendorInterface;
use Sylius\Component\Resource\Model\ResourceInterface;

interface GeneratedFileInterface extends ResourceInterface
{
    public function getVendor(): VendorInterface|null;

    public function setVendor(VendorInterface|null $vendor): void;

    public function getName(): string;

    public function setName(string $name): void;

    public function getPath(): string;

    public function setPath(string $path): void;

    public function getStartDate(): DateTimeInterface;

    public function setStartDate(DateTimeInterface $startDate): void;

    public function getEndDate(): DateTimeInterface;

    public function setEndDate(DateTimeInterface $endDate): void;

    public function getTotalLines(): int|null;

    public function setTotalLines(int|null $totalLines): void;

    public function getTotalCancellations(): int|null;

    public function setTotalCancellations(int|null $totalCancellations): void;

    public function getRegistrationDrawing(): RegistrationDrawingInterface|null;

    public function setRegistrationDrawing(RegistrationDrawingInterface|null $registrationDrawing): void;
}
