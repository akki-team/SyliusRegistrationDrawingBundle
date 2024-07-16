<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TimestampableInterface;

interface DrawingFieldAssociationInterface extends ResourceInterface, TimestampableInterface
{
    public function getDrawingId(): int;

    public function setDrawingId(int $drawingId): void;

    public function getFieldId(): int;

    public function setFieldId(int $fieldId): void;

    public function getOrder(): int|null;

    public function setOrder(int $order): void;

    public function getPosition(): int|null;

    public function setPosition(int $position): void;

    public function getLength(): int|null;

    public function setLength(int $length): void;

    public function getFormat(): string|null;

    public function setFormat(string $format): void;

    public function getSelection(): string|null;

    public function setSelection(string $selection): void;

    public function getName(): string|null;

    public function setName(string $name): void;
}
