<?php

namespace Akki\SyliusRegistrationDrawingBundle\Entity;

trait RegistrationDrawingTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing", inversedBy="vendors")
     * @ORM\JoinColumn(name="registration_drawing_id", referencedColumnName="id", nullable=true, onDelete="CASCADE")
     */
    protected $registrationDrawing;

    /**
     * @return RegistrationDrawing|null
     */
    public function getRegistrationDrawing(): ?RegistrationDrawing
    {
        return $this->registrationDrawing;
    }

    /**
     * @param RegistrationDrawing|null $registrationDrawing
     * @return void
     */
    public function setRegistrationDrawing(?RegistrationDrawing $registrationDrawing): void
    {
        $this->registrationDrawing = $registrationDrawing;
    }

}
