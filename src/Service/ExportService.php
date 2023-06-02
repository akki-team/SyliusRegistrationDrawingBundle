<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Service;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use App\Mailer\Sender\KMSenderInterface;
use League\Csv\ByteSequence;
use League\Csv\Writer;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class ExportService
{
    /** @var KMSenderInterface $emailSender */
    private KMSenderInterface $emailSender;

    /**
     * @param KMSenderInterface $emailSender
     */
    public function __construct(KMSenderInterface $emailSender) {
        $this->emailSender = $emailSender;
    }

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
        $text = '';

        foreach ($fields as $field) {
            $text .= implode('', $field)."\n";
        }

        return $text;
    }

    /**
     * @param RegistrationDrawing $drawing
     * @param string $filePath
     * @param SymfonyStyle $outputStyle
     * @param OutputInterface $output
     * @return bool
     */
    public function sendSalesReportToVendor(
        RegistrationDrawing $drawing,
        string $filePath,
        SymfonyStyle $outputStyle,
        OutputInterface $output
    ): bool
    {
        $success = false;

        $rsaSrc = "~www-data/.ssh/id_rsa";
        $sendMode = $drawing->getSendMode();
        $depositAddress = $drawing->getDepositAddress();
        $user = $drawing->getUser();
        $password = $drawing->getPassword();
        $host = $drawing->getHost();
        $port = $drawing->getPort();

        // si depose SFTP
        if ($sendMode === 'SSH') {
            $command = "echo -e 'put \"$filePath\"' | sftp -o StrictHostKeyChecking=no -i $rsaSrc -P $port $user@$host:\"$depositAddress\"";
        } else {
            $lftpOption = "set sftp:connect-program 'ssh -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no'";
            $command = "lftp -c \"$lftpOption; connect sftp://$user:$password@$host:$port;put -O '$depositAddress' '$filePath'\"";
        }

        $outputStyle->writeln($command);
        $process = Process::fromShellCommandline($command);

        try {
            $process->run() ;
        } catch (\Exception $e) {
            $outputStyle->writeln("Erreur pendant la depose SFTP : ".$e->getMessage());

            $this->sendMail(
                Constants::ERROR_MAIL_CODE,
                Constants::ERROR_MAIL_RECIPIENTS,
                ['fileName' => basename($filePath), 'error' => $e->getMessage()]
            );
        } finally {
            if (!$process->isSuccessful()) {
                $outputStyle->writeln("Erreur pendant la depose SFTP");
            } else {
                $success = true;
                $outputStyle->writeln("Dépose SFTP avec succès");
            }
        }

        return $success;
    }

    /**
     * @param string $mailCode
     * @param array $to
     * @param array $datas
     * @return void
     */
    public function sendMail(string $mailCode, array $to, array $datas): void
    {
        $this->emailSender->send($mailCode, $to, $datas, [], [], [], []);
    }
}
