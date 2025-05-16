<?php

namespace Akki\SyliusRegistrationDrawingBundle\Resolver;

use Sylius\Component\Core\Model\OrderItemInterface;
use Sylius\Component\Core\OrderPaymentStates;
use Sylius\RefundPlugin\Model\RefundType;
use Sylius\RefundPlugin\Provider\RemainingTotalProviderInterface;

class OrderItemMovementTypeResolver implements OrderItemMovementTypeResolverInterface
{
    public function __construct(private RemainingTotalProviderInterface $remainingTotalProvider)
    {
    }

    public function isOrderItemCanceled(OrderItemInterface $orderItem): bool
    {
        if ($orderItem->getOrder()->getPaymentState() === OrderPaymentStates::STATE_REFUNDED) {
            return true;
        }

        // OrderItem is considered canceled only when all item units have been fully refunded
        $refundType = RefundType::orderItemUnit();
        foreach ($orderItem->getUnits() as $orderItemUnit) {
            if ($this->remainingTotalProvider->getTotalLeftToRefund($orderItemUnit->getId(), $refundType) > 0) {
                return false;
            }
        }

        return true;
    }
}
