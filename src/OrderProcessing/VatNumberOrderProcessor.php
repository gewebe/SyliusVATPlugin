<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\OrderProcessing;

use Gweb\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Sylius\Component\Core\Model\ShopBillingDataInterface;
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
        /** @var ChannelInterface $channel */
        $channel = $order->getChannel();

        /** @var ShopBillingDataInterface $shopBillingData */
        $shopBillingData = $channel->getShopBillingData();
        if ($shopBillingData == null) {
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
