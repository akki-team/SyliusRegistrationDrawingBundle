<?php

namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\TimestampableTrait;

class OutputFormats implements OutputFormatsInterface
{
    use TimestampableTrait;

    protected int|null $id = null;

    protected string $type;

    protected string|null $format = null;

    protected Collection|ArrayCollection $fields;

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getFormat(): string|null
    {
        return $this->format;
    }

    public function setFormat(string|null $format): void
    {
        $this->format = $format;
    }

    public function getFields(): Collection|ArrayCollection
    {
        return $this->fields;
    }

    public function setFields(Collection|ArrayCollection $fields): void
    {
        $this->fields = $fields;
    }
}
