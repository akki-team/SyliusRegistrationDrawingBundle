<?php

namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\TimestampableTrait;

class RegistrationDrawing implements RegistrationDrawingInterface
{
    use TimestampableTrait;

    protected int|null $id = null;

    protected string $name;

    protected string $format;

    protected string|null $delimiter = null;

    protected string $periodicity;

    protected string $day;

    protected string $sendMode;

    protected string $depositAddress;

    protected string $user;

    protected string $host;

    protected int $port;

    protected string|null $password = null;

    protected string $recipients;

    protected string|null $currencyFormat = null;

    protected string|null $currencyDelimiter = null;

    protected Collection|ArrayCollection $vendors;

    protected Collection|ArrayCollection $generatedFiles;

    protected Collection|ArrayCollection $titles;

    public function __construct()
    {
        $this->vendors = new ArrayCollection();
        $this->generatedFiles = new ArrayCollection();
        $this->titles = new ArrayCollection();
    }

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
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

    public function getPeriodicity(): string
    {
        return $this->periodicity;
    }

    public function setPeriodicity(string $periodicity): void
    {
        $this->periodicity = $periodicity;
    }

    public function getDay(): string
    {
        return $this->day;
    }

    public function setDay(string $day): void
    {
        $this->day = $day;
    }

    public function getSendMode(): string
    {
        return $this->sendMode;
    }

    public function setSendMode(string $sendMode): void
    {
        $this->sendMode = $sendMode;
    }

    public function getDepositAddress(): string
    {
        return $this->depositAddress;
    }

    public function setDepositAddress(string $depositAddress): void
    {
        $this->depositAddress = $depositAddress;
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): void
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

    public function getHost(): string
    {
        return $this->host;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function setPort(int $port): void
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

    public function getRecipients(): string
    {
        return $this->recipients;
    }

    public function setRecipients(string $recipients): void
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

    public function __toString(): string
    {
        return $this->getName();
    }

}
