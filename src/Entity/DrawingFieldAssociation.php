<?php

namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Sylius\Component\Resource\Model\TimestampableTrait;

class DrawingFieldAssociation implements DrawingFieldAssociationInterface
{
    use TimestampableTrait;

    protected int|null $id = null;

    protected int $drawingId;

    protected int $fieldId;

    protected int|null $order = null;

    protected int|null $position = null;

    protected int|null $length = null;

    protected string|null $format = null;

    protected string|null $selection = null;

    protected string|null $name = null;

    public function getId(): int|null
    {
        return $this->id;
    }

    public function getDrawingId(): int
    {
        return $this->drawingId;
    }

    public function setDrawingId(int $drawingId): void
    {
        $this->drawingId = $drawingId;
    }

    public function getFieldId(): int
    {
        return $this->fieldId;
    }

    public function setFieldId(int $fieldId): void
    {
        $this->fieldId = $fieldId;
    }

    public function getOrder(): int|null
    {
        return $this->order;
    }

    public function setOrder(int|null $order): void
    {
        $this->order = $order;
    }

    public function getPosition(): int|null
    {
        return $this->position;
    }

    public function setPosition(int|null $position): void
    {
        $this->position = $position;
    }

    public function getLength(): int|null
    {
        return $this->length;
    }

    public function setLength(int|null $length): void
    {
        $this->length = $length;
    }

    public function getFormat(): string|null
    {
        return $this->format;
    }

    public function setFormat(string|null $format): void
    {
        $this->format = $format;
    }

    public function getSelection(): string|null
    {
        return $this->selection;
    }

    public function setSelection(string|null $selection): void
    {
        $this->selection = $selection;
    }

    public function getName(): string|null
    {
        return $this->name;
    }

    public function setName(string|null $name): void
    {
        $this->name = $name;
    }

}
