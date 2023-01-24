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
     * @return mixed
     */
    public function getRegistrationDrawing()
    {
        return $this->registrationDrawing;
    }

    /**
     * @param mixed $registrationDrawing
     */
    public function setRegistrationDrawing($registrationDrawing): void
    {
        $this->registrationDrawing = $registrationDrawing;
    }

}
