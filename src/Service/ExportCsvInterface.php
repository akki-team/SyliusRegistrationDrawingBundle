<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Service;

use League\Csv\Writer;

interface ExportCsvInterface
{
    public function exportCSV(array $header, array $lines, string $delimiter = ';', bool $withBom = true): Writer;

    public function exportFixedLength(array $fields): string;
}
