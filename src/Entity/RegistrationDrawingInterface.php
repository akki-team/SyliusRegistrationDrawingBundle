<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

interface RegistrationDrawingInterface extends ResourceInterface, TimestampableInterface
{
    public function getName(): string|null;

    public function setName(string|null $name): void;

    public function getFormat(): string|null;

    public function setFormat(string|null $format): void;

    public function getDelimiter(): string|null;

    public function setDelimiter(string|null $delimiter): void;

    public function getPeriodicity(): string|null;

    public function setPeriodicity(string|null $periodicity): void;

    public function getDay(): string|null;

    public function setDay(string|null $day): void;

    public function getSendMode(): string|null;

    public function setSendMode(string|null $sendMode): void;

    public function getDepositAddress(): string|null;

    public function setDepositAddress(string|null $depositAddress): void;

    public function getUser(): string|null;

    public function setUser(string|null $user): void;

    public function getVendors(): Collection|ArrayCollection;

    public function setVendors(Collection|ArrayCollection $vendors): void;

    public function getTitles(): Collection|ArrayCollection;

    public function setTitles(Collection|ArrayCollection $titles): void;

    public function getGeneratedFiles(): Collection|ArrayCollection;

    public function setGeneratedFiles(Collection|ArrayCollection $generatedFiles): void;

    public function getHost(): string|null;

    public function setHost(string|null $host): void;

    public function getPort(): int|null;

    public function setPort(int|null $port): void;

    public function getPassword(): string|null;

    public function setPassword(string|null $password): void;

    public function getRecipients(): string|null;

    public function setRecipients(string|null $recipients): void;

    public function getCurrencyFormat(): string|null;

    public function setCurrencyFormat(string|null $currencyFormat): void;

    public function getCurrencyDelimiter(): string|null;

    public function setCurrencyDelimiter(string|null $currencyDelimiter): void;

    public function getEncoding(): int;

    public function setEncoding(int $encoding): void;
}
