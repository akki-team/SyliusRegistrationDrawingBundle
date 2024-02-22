<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

interface DrawingFieldInterface extends ResourceInterface, TimestampableInterface
{
    public function getName(): string;

    public function setName(string $name): void;

    public function getEquivalent(): string|null;

    public function setEquivalent(string $equivalent): void;

    public function getType(): OutputFormatsInterface;

    public function setType(OutputFormatsInterface $type): void;
}
