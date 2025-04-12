<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\OrderProcessing;

use Doctrine\ORM\EntityManagerInterface;
use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Addressing\Repository\ZoneRepositoryInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Resolver\TaxationAddressResolverInterface;
use Sylius\Component\Order\Model\OrderInterface;
use Sylius\Component\Order\Processor\OrderProcessorInterface;

/**
 * Recalculates the order without VAT tax
 */
final class VatNumberOrderProcessor implements OrderProcessorInterface
{
    private ?ZoneInterface $euZone;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private ZoneRepositoryInterface $zoneRepository,
        private TaxationAddressResolverInterface $taxationAddressResolver,
        private bool $isActive = true,
    ) {
    }

    /**
     * @param \Sylius\Component\Core\Model\OrderInterface $order
     *
     * @phpstan-ignore-next-line
     */
    public function process(OrderInterface $order): void
    {
        $this->euZone = $this->getEuZone();

        if (!$this->isActive || $this->euZone === null) {
            return;
        }

        if ($this->isValidForZeroTax($order)) {
            $this->removeIncludedTaxes($order);

            $order->removeAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT);
        }
    }

    private function removeIncludedTaxes(OrderInterface $order): void
    {
        foreach ($order->getAdjustments(AdjustmentInterface::TAX_ADJUSTMENT) as $taxAdjustment) {
            if ($taxAdjustment->isNeutral()) {
                foreach ($order->getAdjustments(AdjustmentInterface::SHIPPING_ADJUSTMENT) as $shipmentAdjustment) {
                    if ($shipmentAdjustment->getDetails()['shippingMethodCode'] == $taxAdjustment->getDetails()['shippingMethodCode']) {
                        $shipmentAdjustment->setAmount($shipmentAdjustment->getAmount() - $taxAdjustment->getAmount());
                    }
                }
            }
        }

        foreach ($order->getItems() as $item) {
            $includedTaxes = 0;
            foreach ($item->getAdjustmentsRecursively(AdjustmentInterface::TAX_ADJUSTMENT) as $taxAdjustment) {
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
     */
    private function isValidForZeroTax(OrderInterface $order): bool
    {
        $channel = $order->getChannel();
        if ($channel === null) {
            return false;
        }

        $shopBillingData = $channel->getShopBillingData();
        if ($shopBillingData === null ||
            !$this->isEuZone($shopBillingData->getCountryCode())) {
            return false;
        }

        $taxationAddress = $this->taxationAddressResolver->getTaxationAddressFromOrder($order);

        if ($taxationAddress instanceof VatNumberAddressInterface &&
            $taxationAddress->hasValidVatNumber() &&
            $this->isEuZone($taxationAddress->getCountryCode()) &&
            $taxationAddress->getCountryCode() !== $shopBillingData->getCountryCode()) {
            return true;
        }

        return false;
    }

    private function getEuZone(): ?ZoneInterface
    {
        /** @var ZoneInterface|null $euZone */
        $euZone = $this->zoneRepository->findOneBy(['code' => 'EU', 'scope' => Scope::ALL]);

        // @fixme ZoneRepository finds Zone properly with all members, after OrderTaxesProcessor have been executed
        if ($euZone instanceof ZoneInterface) {
            $this->entityManager->refresh($euZone);
        }

        return $euZone;
    }

    private function isEuZone(?string $countyCode): bool
    {
        if ($countyCode === null || $this->euZone === null) {
            return false;
        }

        foreach ($this->euZone->getMembers() as $member) {
            $zoneMemberCode = $member->getCode();
            if (null !== $zoneMemberCode) {
                $posDivider = strpos($zoneMemberCode, '-');

                if ($posDivider !== false) {
                    $zoneMemberCode = substr($zoneMemberCode, 0, $posDivider);
                }
            }

            if ($zoneMemberCode === $countyCode) {
                return true;
            }
        }

        return false;
    }
}
