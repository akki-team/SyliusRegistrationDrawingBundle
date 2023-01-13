<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Event;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class RegistrationDrawingMenuBuilderEvent extends MenuBuilderEvent
{
    /** @var RegistrationDrawing */
    private $registrationDrawing;

    public function __construct(FactoryInterface $factory, ItemInterface $menu, RegistrationDrawing $registrationDrawing)
    {
        parent::__construct($factory, $menu);

        $this->registrationDrawing = $registrationDrawing;
    }

    public function getRegistrationDrawing(): RegistrationDrawing
    {
        return $this->registrationDrawing;
    }
}
