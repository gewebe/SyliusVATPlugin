<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\OrderProcessing;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Resolver\TaxationAddressResolverInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

/**
 * Recalculates the order without VAT tax
 */
final class VatNumberOrderProcessor implements OrderProcessorInterface
{
    private ?ZoneInterface $euZone;

    public function __construct(
        private RepositoryInterface $zoneRepository,
        private TaxationAddressResolverInterface $taxationAddressResolver,
        private bool $isActive = true
    ) {
        $this->euZone = $this->getEuZone();
    }

    public function process(OrderInterface $order): void
    {
        if (!$this->isActive || $this->euZone === null) {
            return;
        }

        /** @var \Sylius\Component\Core\Model\OrderInterface $order */
        if ($this->isValidForZeroTax($order)) {
            $this->removeIncludedTaxes($order);

            $order->removeAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT);
        }
    }

    private function removeIncludedTaxes(OrderInterface $order): void
    {
        foreach($order->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT) as $taxAdjustment)
        {
            if ($taxAdjustment->isNeutral()) {
                foreach($order->getAdjustments(AdjustmentInterface::SHIPPING_ADJUSTMENT) as $shipmentAdjustment) 
                {
                    if ($shipmentAdjustment->getDetails()['shippingMethodCode'] == $taxAdjustment->getDetails()['shippingMethodCode']) {
                        $shipmentAdjustment->setAmount($shipmentAdjustment->getAmount() - $taxAdjustment->getAmount());
                    }
                }
            }
        }

        foreach($order->getItems() as $item)
        {
            $includedTaxes = 0;
            foreach($item->getAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT) as $taxAdjustment)
            {
                if ($taxAdjustment->isNeutral()) {
                    $includedTaxes += $taxAdjustment->getAmount();
                }
            }

            if ($includedTaxes > 0) {
                $unitTax = (int) floor($includedTaxes / $item->getQuantity());

                $item->setUnitPrice($item->getUnitPrice() - $unitTax);
                $item->recalculateUnitsTotal();
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
        if ($shopBillingData === null
            || !$this->isEuZone($shopBillingData->getCountryCode())) {
            return false;
        }

        $taxationAddress = $this->taxationAddressResolver->getTaxationAddressFromOrder($order);

        if ($taxationAddress instanceof VatNumberAddressInterface
            && $taxationAddress->hasValidVatNumber()
            && $this->isEuZone($taxationAddress->getCountryCode())
            && $taxationAddress->getCountryCode() !== $shopBillingData->getCountryCode()) {
            return true;
        }

        return false;
    }

    private function getEuZone(): ?ZoneInterface
    {
        /** @var ZoneInterface|null $euZone */
        $euZone = $this->zoneRepository->findOneBy(['code' => 'EU', 'scope' => Scope::ALL]);

        return $euZone;
    }

    private function isEuZone(?string $countyCode): bool
    {
        if ($countyCode === null || $this->euZone === null) {
            return false;
        }

        foreach ($this->euZone->getMembers() as $member) {
            if ($member->getCode() === $countyCode) {
                return true;
            }
        }

        return false;
    }
}
