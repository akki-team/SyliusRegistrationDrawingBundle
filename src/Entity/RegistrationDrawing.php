<?php

namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="registration_drawing")
 */
class RegistrationDrawing implements ResourceInterface, TimestampableInterface
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer", nullable=true, name="id")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=false, name="name")
     */
    private $name;

    /**
     * @ORM\Column(type="string", nullable=false, name="format")
     */
    private $format;

    /**
     * @ORM\Column(type="string", nullable=true, name="delimiter")
     */
    private $delimiter;

    /**
     * @ORM\Column(type="string", nullable=false, name="periodicity")
     */
    private $periodicity;

    /**
     * @ORM\Column(type="string", nullable=false, name="day")
     */
    private $day;

    /**
     * @ORM\Column(type="string", nullable=false, name="send_mode")
     */
    private $sendMode;

    /**
     * @ORM\Column(type="string", nullable=false, name="deposit_address")
     */
    private $depositAddress;

    /**
     * @ORM\Column(type="string", nullable=false, name="user")
     */
    private $user;

    /**
     * @ORM\Column(type="string", nullable=false, name="host")
     */
    private $host;

    /**
     * @ORM\Column(type="integer", nullable=false, name="port")
     */
    private $port;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Vendor\Vendor",
     *     mappedBy="registrationDrawing",
     *     orphanRemoval=true
     * )
     */
    private $vendors;

    /**
     * @ORM\Column(type="string", nullable=true, name="password")
     */
    private $password;

    /**
     * @ORM\Column(type="text", nullable=false, name="recipients")
     */
    private $recipients;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\EditorExports\GeneratedFile",
     *     mappedBy="registrationDrawing",
     *     orphanRemoval=true
     * )
     */
    private $generatedFiles;

    /**
     * @ORM\Column(type="string", nullable=true, name="currency_format")
     */
    private $currencyFormat;

    /**
     * @ORM\Column(type="string", nullable=true, name="currency_delimiter")
     */
    private $currencyDelimiter;

    /**
     * @ORM\OneToMany(
     *     targetEntity="App\Entity\Taxonomy\Taxon",
     *     mappedBy="registrationDrawing",
     *     orphanRemoval=true
     * )
     */
    private $titles;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=false, name="created_at")
     */
    private $createdAt;

    /**
     * @var DateTime
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=false, name="updated_at")
     */
    private $updatedAt;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getFormat(): ?string
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    /**
     * @return string|null
     */
    public function getDelimiter(): ?string
    {
        return $this->delimiter;
    }

    /**
     * @param string|null $delimiter
     */
    public function setDelimiter(?string $delimiter): void
    {
        $this->delimiter = $delimiter;
    }

    /**
     * @return string
     */
    public function getPeriodicity(): ?string
    {
        return $this->periodicity;
    }

    /**
     * @param string $periodicity
     */
    public function setPeriodicity(string $periodicity): void
    {
        $this->periodicity = $periodicity;
    }

    /**
     * @return string
     */
    public function getDay(): ?string
    {
        return $this->day;
    }

    /**
     * @param string $day
     */
    public function setDay(string $day): void
    {
        $this->day = $day;
    }

    /**
     * @return string
     */
    public function getSendMode(): ?string
    {
        return $this->sendMode;
    }

    /**
     * @param string $sendMode
     */
    public function setSendMode(string $sendMode): void
    {
        $this->sendMode = $sendMode;
    }

    /**
     * @return string
     */
    public function getDepositAddress(): ?string
    {
        return $this->depositAddress;
    }

    /**
     * @param string $depositAddress
     */
    public function setDepositAddress(string $depositAddress): void
    {
        $this->depositAddress = $depositAddress;
    }

    /**
     * @return string
     */
    public function getUser(): ?string
    {
        return $this->user;
    }

    /**
     * @param string $user
     */
    public function setUser(string $user): void
    {
        $this->user = $user;
    }

    /**
     * @return mixed
     */
    public function getVendors()
    {
        return $this->vendors;
    }

    /**
     * @param mixed $vendors
     */
    public function setVendors($vendors): void
    {
        $this->vendors = $vendors;
    }

    /**
     * @return mixed
     */
    public function getGeneratedFiles()
    {
        return $this->generatedFiles;
    }

    /**
     * @param mixed $generatedFiles
     */
    public function setGeneratedFiles($generatedFiles): void
    {
        $this->generatedFiles = $generatedFiles;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getCreatedAt(): ?DateTimeInterface
    {
        return $this->createdAt;
    }

    /**
     * @param DateTimeInterface|null $createdAt
     */
    public function setCreatedAt(?DateTimeInterface $createdAt): void
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getUpdatedAt(): ?DateTimeInterface
    {
        return $this->updatedAt;
    }

    /**
     * @param DateTimeInterface|null $updatedAt
     */
    public function setUpdatedAt(?DateTimeInterface $updatedAt): void
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * @return mixed
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param mixed $host
     */
    public function setHost($host): void
    {
        $this->host = $host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param int $port
     */
    public function setPort(int $port): void
    {
        $this->port = $port;
    }

    /**
     * @return string|null
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    /**
     * @param string|null $password
     * @return void
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string|null
     */
    public function getRecipients(): ?string
    {
        return $this->recipients;
    }

    /**
     * @param string $recipients
     */
    public function setRecipients(string $recipients): void
    {
        $this->recipients = $recipients;
    }

    /**
     * @return string|null
     */
    public function getCurrencyFormat(): ?string
    {
        return $this->currencyFormat;
    }

    /**
     * @param string $currencyFormat
     */
    public function setCurrencyFormat(string $currencyFormat): void
    {
        $this->currencyFormat = $currencyFormat;
    }

    /**
     * @return string|null
     */
    public function getCurrencyDelimiter(): ?string
    {
        return $this->currencyDelimiter;
    }

    /**
     * @param string $currencyDelimiter
     */
    public function setCurrencyDelimiter(string $currencyDelimiter): void
    {
        $this->currencyDelimiter = $currencyDelimiter;
    }

    /**
     * @return mixed
     */
    public function getTitles()
    {
        return $this->titles;
    }

    /**
     * @param mixed $titles
     */
    public function setTitles($titles): void
    {
        $this->titles = $titles;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getName();
    }

}
