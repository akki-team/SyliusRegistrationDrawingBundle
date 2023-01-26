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
     * @param string $path
     * @return void
     */
    public function exportFixedLength(array $fields, string $path)
    {
        $text = '';
        $fields = implode('', $fields) ;
        $text .= $fields."\n";

        file_put_contents($path, $text);
    }
}
