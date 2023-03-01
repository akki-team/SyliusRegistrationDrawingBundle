<?php

namespace Akki\SyliusRegistrationDrawingBundle\Entity;

trait RegistrationDrawingTaxonTrait
{
    /**
     * @ORM\ManyToOne(targetEntity="Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing", inversedBy="titles")
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
