<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Event;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawingInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

class RegistrationDrawingMenuBuilderEvent extends MenuBuilderEvent
{
    private readonly RegistrationDrawingInterface $registrationDrawing;

    public function __construct(FactoryInterface $factory, ItemInterface $menu, RegistrationDrawingInterface $registrationDrawing)
    {
        parent::__construct($factory, $menu);

        $this->registrationDrawing = $registrationDrawing;
    }

    public function getRegistrationDrawing(): RegistrationDrawingInterface
    {
        return $this->registrationDrawing;
    }
}
