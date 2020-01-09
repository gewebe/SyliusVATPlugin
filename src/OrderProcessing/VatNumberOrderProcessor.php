<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\OrderProcessing;

use Gweb\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Channel;
use Sylius\Component\Core\Model\ShopBillingData;
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

    /**
     * @param \Sylius\Component\Core\Model\OrderInterface $order
     */
    public function process(OrderInterface $order): void
    {
        if (!$this->isActive) {
            return;
        }

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
        /** @var Channel $channel */
        $channel = $order->getChannel();

        /** @var ShopBillingData $shopBillingData */
        $shopBillingData = $channel->getShopBillingData();
        if (null === $shopBillingData) {
            return false;
        }

        /** @var VatNumberAddressInterface $billingAddress */
        $billingAddress = $order->getBillingAddress();

        if ($billingAddress instanceof VatNumberAddressInterface
            && $billingAddress->hasValidVatNumber()
            && $billingAddress->getCountryCode() !== $shopBillingData->getCountryCode()) {
            return true;
        }

        return false;
    }
}
