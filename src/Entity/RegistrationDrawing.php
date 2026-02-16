<?php

namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Akki\SyliusRegistrationDrawingBundle\Helpers\Constants;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\TimestampableTrait;

class RegistrationDrawing implements RegistrationDrawingInterface
{
    use TimestampableTrait;

    protected int|null $id = null;

    protected string|null $name = null;

    protected string|null $format = null;

    protected string|null $delimiter = null;

    protected string|null $periodicity = null;

    protected string|null $day = null;

    protected string|null $sendMode = null;

    protected string|null $depositAddress = null;

    protected string|null $user = null;

    protected string|null $host = null;

    protected int|null $port = null;

    protected string|null $password = null;

    protected string|null $recipients = null;

    protected string|null $currencyFormat = null;

    protected string|null $currencyDelimiter = null;

    protected Collection|ArrayCollection $vendors;

    protected Collection|ArrayCollection $generatedFiles;

    protected Collection|ArrayCollection $titles;

    protected int $encoding;

    public function __construct()
    {
        $this->vendors = new ArrayCollection();
        $this->generatedFiles = new ArrayCollection();
        $this->titles = new ArrayCollection();
        $this->encoding = Constants::ENCODING_UTF8;
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function setName(string|null $name): void
    {
        $this->name = $name;
    }

    public function getFormat(): string|null
    {
        return $this->format;
    }

    public function setFormat(string|null $format): void
    {
        $this->format = $format;
    }

    public function getDelimiter(): string|null
    {
        return $this->delimiter;
    }

    public function setDelimiter(string|null $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    public function getPeriodicity(): string|null
    {
        return $this->periodicity;
    }

    public function setPeriodicity(string|null $periodicity): void
    {
        $this->periodicity = $periodicity;
    }

    public function getDay(): string|null
    {
        return $this->day;
    }

    public function setDay(string|null $day): void
    {
        $this->day = $day;
    }

    public function getSendMode(): string|null
    {
        return $this->sendMode;
    }

    public function setSendMode(string|null $sendMode): void
    {
        $this->sendMode = $sendMode;
    }

    public function getDepositAddress(): string|null
    {
        return $this->depositAddress;
    }

    public function setDepositAddress(string|null $depositAddress): void
    {
        $this->depositAddress = $depositAddress;
    }

    public function getUser(): string|null
    {
        return $this->user;
    }

    public function setUser(string|null $user): void
    {
        $this->user = $user;
    }

    public function getVendors(): Collection|ArrayCollection
    {
        return $this->vendors;
    }

    public function setVendors(Collection|ArrayCollection $vendors): void
    {
        $this->vendors = $vendors;
    }

    public function getGeneratedFiles(): Collection|ArrayCollection
    {
        return $this->generatedFiles;
    }

    public function setGeneratedFiles(Collection|ArrayCollection $generatedFiles): void
    {
        $this->generatedFiles = $generatedFiles;
    }

    public function getHost(): string|null
    {
        return $this->host;
    }

    public function setHost(string|null $host): void
    {
        $this->host = $host;
    }

    public function getPort(): int|null
    {
        return $this->port;
    }

    public function setPort(int|null $port): void
    {
        $this->port = $port;
    }

    public function getPassword(): string|null
    {
        return $this->password;
    }

    public function setPassword(string|null $password): void
    {
        $this->password = $password;
    }

    public function getRecipients(): string|null
    {
        return $this->recipients;
    }

    public function setRecipients(string|null $recipients): void
    {
        $this->recipients = $recipients;
    }

    public function getCurrencyFormat(): string|null
    {
        return $this->currencyFormat;
    }

    public function setCurrencyFormat(string|null $currencyFormat): void
    {
        $this->currencyFormat = $currencyFormat;
    }

    public function getCurrencyDelimiter(): string|null
    {
        return $this->currencyDelimiter;
    }

    public function setCurrencyDelimiter(string|null $currencyDelimiter): void
    {
        $this->currencyDelimiter = $currencyDelimiter;
    }

    public function getTitles(): Collection|ArrayCollection
    {
        return $this->titles;
    }

    public function setTitles(Collection|ArrayCollection $titles): void
    {
        $this->titles = $titles;
    }

    /**
     * @return int
     */
    public function getEncoding(): int
    {
        return $this->encoding;
    }

    /**
     * @param int $encoding
     */
    public function setEncoding(int $encoding): void
    {
        $this->encoding = $encoding;
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }
}
