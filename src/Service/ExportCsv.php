<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Service;

use League\Csv\ByteSequence;
use League\Csv\Writer;

final class ExportCsv implements ExportCsvInterface
{
    public function exportCSV(array $header, array $lines, string $delimiter = ';', bool $withBom = true): Writer
    {
        $writer = Writer::createFromStream(fopen('php://temp', 'rb+'));
        $writer->setDelimiter($delimiter);
        if ($withBom) {
            $writer->setOutputBOM(ByteSequence::BOM_UTF8);
        } else {
            $writer->setEndOfLine("\r\n");
        }
        $writer->insertOne($header);
        $writer->insertAll($lines);

        return $writer;
    }

    public function exportFixedLength(array $fields, bool $withBom = true): string
    {
        $text = '';

        $endOfLine = $withBom ? "\n" : "\r\n";

        foreach ($fields as $field) {
            $text .= implode('', $field) . $endOfLine;
        }

        return $text;
    }
}
