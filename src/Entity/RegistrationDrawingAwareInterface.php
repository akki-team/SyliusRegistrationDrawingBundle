<?php
declare(strict_types=1);


namespace Akki\SyliusRegistrationDrawingBundle\Entity;

interface RegistrationDrawingAwareInterface
{
    public function getRegistrationDrawing(): RegistrationDrawingInterface|null;

    public function setRegistrationDrawing(RegistrationDrawingInterface|null $registrationDrawing): void;
}
