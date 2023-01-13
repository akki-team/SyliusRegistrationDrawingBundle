<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('sylius_registration_drawing_bundle');

        $treeBuilder->getRootNode();

        return $treeBuilder;
    }
}
