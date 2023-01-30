<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Service;

use League\Csv\ByteSequence;
use League\Csv\Writer;

class ExportService
{
    /**
     * @param array $header
     * @param array $lines
     * @param string $delimiter
     * @return Writer
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\Exception
     */
    public function exportCSV(array $header, array $lines, string $delimiter = ';'): Writer
    {
        $writer = Writer::createFromStream(fopen('php://temp', 'rb+'));
        $writer->setDelimiter($delimiter);
        $writer->setOutputBOM(ByteSequence::BOM_UTF8);
        $writer->insertOne($header);
        $writer->insertAll($lines);

        return $writer;
    }

    /**
     * @param array $fields
     * @return string
     */
    public function exportFixedLength(array $fields): string
    {
        return implode('', $fields)."\n";
    }
}
