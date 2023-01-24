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
     * @return Writer
     * @throws \League\Csv\CannotInsertRecord
     * @throws \League\Csv\InvalidArgument
     */
    public function exportCSV(array $header, array $lines): Writer
    {
        $writer = Writer::createFromStream(fopen('php://temp', 'rb+'));
        $writer->setDelimiter(';');
        $writer->setOutputBOM(ByteSequence::BOM_UTF8);

        $writer->insertOne($header);

        $writer->insertAll($lines);

        return $writer;
    }

    public function exportFixedLength()
    {

    }
}
