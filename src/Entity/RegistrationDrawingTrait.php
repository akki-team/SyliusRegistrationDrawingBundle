<?php

namespace Akki\SyliusRegistrationDrawingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait RegistrationDrawingTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing", inversedBy="vendors")
     * @ORM\JoinColumn(name="registration_drawing_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected RegistrationDrawingInterface|null $registrationDrawing = null;

    public function getRegistrationDrawing(): RegistrationDrawingInterface|null
    {
        return $this->registrationDrawing;
    }

    public function setRegistrationDrawing(RegistrationDrawingInterface|null $registrationDrawing): void
    {
        $this->registrationDrawing = $registrationDrawing;
    }

}
