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
     * @ORM\Column(type="integer", name="id")
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
     * @return int
     */
    public function getId(): int
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

}
