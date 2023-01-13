<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Menu;

use Akki\SyliusRegistrationDrawingBundle\Entity\RegistrationDrawing;
use Akki\SyliusRegistrationDrawingBundle\Event\RegistrationDrawingMenuBuilderEvent;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class RegistrationDrawingFormMenuBuilder
{
    public const EVENT_NAME = 'sylius.menu.admin.registration_drawing.form';

    /** @var FactoryInterface */
    private $factory;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    public function __construct(FactoryInterface $factory, EventDispatcherInterface $eventDispatcher)
    {
        $this->factory = $factory;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function createMenu(array $options = []): ItemInterface
    {
        $menu = $this->factory->createItem('root');

        if (!array_key_exists('registration_drawing', $options) || !$options['registration_drawing'] instanceof RegistrationDrawing) {
            return $menu;
        }

        $menu
            ->addChild('general')
            ->setAttribute('template', '@RegistrationDrawingBundle/Resources/views/Tab/_general.html.twig')
            ->setLabel('sylius_registration_drawing.admin.bloc_general')
            ->setCurrent(true)
        ;

        $menu
            ->addChild('fields')
            ->setAttribute('template', '@RegistrationDrawingBundle/Resources/views/Tab/_fields.twig')
            ->setLabel('sylius_registration_drawing.admin.bloc_fields')
        ;

        $this->eventDispatcher->dispatch(
            self::EVENT_NAME,
            new RegistrationDrawingMenuBuilderEvent($this->factory, $menu, $options['registration_drawing'])
        );

        return $menu;
    }
}
