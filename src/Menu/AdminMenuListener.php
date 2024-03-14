<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Menu;

use Sylius\Bundle\UiBundle\Menu\Event\MenuBuilderEvent;

final class AdminMenuListener
{
    /**
     * @param MenuBuilderEvent $event
     */
    public function addAdminMenuItems(MenuBuilderEvent $event): void
    {
        $menu = $event->getMenu();

        /* Admin Registration drawings Menu */
        $menu
            ->getChild('marketplace')
            ->addChild('registration_drawings', ['route' => 'akki_admin_registration_drawing_index'])
            ->setLabel('sylius_registration_drawing.ui.registration_drawings')
            ->setLabelAttribute('icon', 'file alternate');

        $menu
            ->getChild('marketplace')
            ->addChild('generated_files', ['route' => 'akki_admin_generated_file_index'])
            ->setLabel('sylius_registration_drawing.ui.generated_files')
            ->setLabelAttribute('icon', 'file');
    }
}
