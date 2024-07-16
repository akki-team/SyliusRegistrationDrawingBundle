<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Service;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawingInterface;
use DateTimeInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

interface ExportServiceInterface
{
    public function exportDrawing(RegistrationDrawingInterface $registrationDrawing, DateTimeInterface $startDate, DateTimeInterface $endDate, bool $drop = false): void;

    public function sendSalesReportToVendor(RegistrationDrawingInterface $registrationDrawing, string $filePath, SymfonyStyle|null $outputStyle = null): bool;

    public function sendMail(string $mailCode, array $to, array $datas): void;
}
