<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Menu;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawingInterface;
use Akki\SyliusRegistrationDrawingBundle\Event\RegistrationDrawingMenuBuilderEvent;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final readonly class RegistrationDrawingFormMenuBuilder
{
    public const EVENT_NAME = 'sylius.menu.admin.registration_drawing.form';

    public function __construct(
        private FactoryInterface         $factory,
        private EventDispatcherInterface $eventDispatcher
    )
    {
    }

    public function createMenu(array $options = []): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (false === array_key_exists('registration_drawing', $options)) {
            return $menu;
        }

        $registrationDrawing = $options['registration_drawing'];

        if (false === $registrationDrawing instanceof RegistrationDrawingInterface) {
            return $menu;
        }

        $menu
            ->addChild('general')
            ->setAttribute('template', '@AkkiSyliusRegistrationDrawingPlugin/Tab/_general.html.twig')
            ->setLabel('sylius_registration_drawing.admin.bloc_general')
            ->setCurrent(true);

        $menu
            ->addChild('fields')
            ->setAttribute('template', '@AkkiSyliusRegistrationDrawingPlugin/Tab/_fields.html.twig')
            ->setLabel('sylius_registration_drawing.admin.bloc_fields');

        $this->eventDispatcher->dispatch(
            new RegistrationDrawingMenuBuilderEvent($this->factory, $menu, $registrationDrawing),
            self::EVENT_NAME,
        );

        return $menu;
    }
}
