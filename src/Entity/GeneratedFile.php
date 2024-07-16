<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use DateTimeInterface;
use Odiseo\SyliusMarketplacePlugin\Entity\VendorInterface;

class GeneratedFile implements GeneratedFileInterface
{
    protected int|null $id = null;

    protected VendorInterface|null $vendor = null;

    protected string $name;

    protected string $path;

    protected DateTimeInterface $startDate;

    protected DateTimeInterface $endDate;

    protected int|null $totalLines = null;

    protected int|null $totalCancellations = null;

    protected RegistrationDrawingInterface|null $registrationDrawing = null;

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getVendor(): VendorInterface|null
    {
        return $this->vendor;
    }

    public function setVendor(VendorInterface|null $vendor): void
    {
        $this->vendor = $vendor;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    public function getStartDate(): DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface $startDate): void
    {
        $this->startDate = $startDate;
    }

    public function getEndDate(): DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface $endDate): void
    {
        $this->endDate = $endDate;
    }

    public function getTotalLines(): int|null
    {
        return $this->totalLines;
    }

    public function setTotalLines(int|null $totalLines): void
    {
        $this->totalLines = $totalLines;
    }

    public function getTotalCancellations(): int|null
    {
        return $this->totalCancellations;
    }

    public function setTotalCancellations(int|null $totalCancellations): void
    {
        $this->totalCancellations = $totalCancellations;
    }

    public function getRegistrationDrawing(): RegistrationDrawingInterface|null
    {
        return $this->registrationDrawing;
    }

    public function setRegistrationDrawing(RegistrationDrawingInterface|null $registrationDrawing): void
    {
        $this->registrationDrawing = $registrationDrawing;
    }

}
