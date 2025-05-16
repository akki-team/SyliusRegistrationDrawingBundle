<?php

namespace Akki\SyliusRegistrationDrawingBundle\Resolver;

use Sylius\Component\Core\Model\OrderItemInterface;

interface OrderItemMovementTypeResolverInterface
{
    public function isOrderItemCanceled(OrderItemInterface $orderItem): bool;
}
