<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

interface RegistrationDrawingInterface extends ResourceInterface, TimestampableInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getFormat(): string;

    public function setFormat(string $format): void;

    public function getDelimiter(): string|null;

    public function setDelimiter(string|null $delimiter): void;

    public function getPeriodicity(): string;

    public function setPeriodicity(string $periodicity): void;

    public function getDay(): string;

    public function setDay(string $day): void;

    public function getSendMode(): string;

    public function setSendMode(string $sendMode): void;

    public function getDepositAddress(): string;

    public function setDepositAddress(string $depositAddress): void;

    public function getUser(): string;

    public function setUser(string $user): void;

    public function getVendors(): Collection|ArrayCollection;

    public function setVendors(Collection|ArrayCollection $vendors): void;

    public function getTitles(): Collection|ArrayCollection;

    public function setTitles(Collection|ArrayCollection $titles): void;

    public function getGeneratedFiles(): Collection|ArrayCollection;

    public function setGeneratedFiles(Collection|ArrayCollection $generatedFiles): void;

    public function getHost(): string;

    public function setHost(string $host): void;

    public function getPort(): int;

    public function setPort(int $port): void;

    public function getPassword(): string|null;

    public function setPassword(string|null $password): void;

    public function getRecipients(): string;

    public function setRecipients(string $recipients): void;

    public function getCurrencyFormat(): string|null;

    public function setCurrencyFormat(string|null $currencyFormat): void;

    public function getCurrencyDelimiter(): string|null;

    public function setCurrencyDelimiter(string|null $currencyDelimiter): void;

    public function getEncoding(): int;

    public function setEncoding(int $encoding): void;
}
