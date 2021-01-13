<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\OrderProcessing;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;

/**
 * Recalculates the order without VAT tax
 */
final class VatNumberOrderProcessor implements OrderProcessorInterface
{
    /** @var bool */
    private $isActive;

    public function __construct(bool $isActive = true)
    {
        $this->isActive = $isActive;
    }

    public function process(OrderInterface $order): void
    {
        if (!$this->isActive) {
            return;
        }

        /** @var \Sylius\Component\Core\Model\OrderInterface $order */
        if ($this->isValidForZeroTax($order)) {
            foreach ($order->getItems() as $item) {
                $item->removeAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT);
            }
        }
    }

    /**
     * @param \Sylius\Component\Core\Model\OrderInterface $order
     * @return bool
     */
    private function isValidForZeroTax(OrderInterface $order): bool
    {
        $channel = $order->getChannel();
        if ($channel === null) {
            return false;
        }

        $shopBillingData = $channel->getShopBillingData();
        if ($shopBillingData === null) {
            return false;
        }

        $billingAddress = $order->getBillingAddress();

        if ($billingAddress instanceof VatNumberAddressInterface
            && $billingAddress->hasValidVatNumber()
            && $billingAddress->getCountryCode() !== $shopBillingData->getCountryCode()) {
            return true;
        }

        return false;
    }
}
