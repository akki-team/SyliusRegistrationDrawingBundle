<?php

namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Sylius\Component\Resource\Model\TimestampableTrait;

class DrawingField implements DrawingFieldInterface
{
    use TimestampableTrait;

    protected int|null $id = null;

    protected string $name;

    protected string|null $equivalent = null;

    protected OutputFormatsInterface $type;

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

    public function getEquivalent(): string|null
    {
        return $this->equivalent;
    }

    public function setEquivalent(string|null $equivalent): void
    {
        $this->equivalent = $equivalent;
    }

    public function getType(): OutputFormatsInterface
    {
        return $this->type;
    }

    public function setType(OutputFormatsInterface $type): void
    {
        $this->type = $type;
    }
}
