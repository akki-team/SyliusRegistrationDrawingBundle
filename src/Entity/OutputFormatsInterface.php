<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

interface OutputFormatsInterface extends ResourceInterface, TimestampableInterface
{
    public function getType(): string;

    public function setType(string $type): void;

    public function getFormat(): string|null;

    public function setFormat(string|null $format): void;

    public function getFields(): Collection|ArrayCollection;

    public function setFields(Collection|ArrayCollection $fields): void;
}
