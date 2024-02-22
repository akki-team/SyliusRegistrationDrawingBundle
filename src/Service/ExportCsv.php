<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Service;

use League\Csv\ByteSequence;
use League\Csv\Writer;

final class ExportCsv implements ExportCsvInterface
{
    public function exportCSV(array $header, array $lines, string $delimiter = ';'): Writer
    {
        $writer = Writer::createFromStream(fopen('php://temp', 'rb+'));
        $writer->setDelimiter($delimiter);
        $writer->setOutputBOM(ByteSequence::BOM_UTF8);
        $writer->insertOne($header);
        $writer->insertAll($lines);

        return $writer;
    }

    public function exportFixedLength(array $fields): string
    {
        $text = '';

        foreach ($fields as $field) {
            $text .= implode('', $field) . "\n";
        }

        return $text;
    }
}
