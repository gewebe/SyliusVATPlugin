<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\OrderProcessing;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\AdjustmentInterface;
use Sylius\Component\Core\Model\Scope;
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
        if ($shopBillingData === null
            || !$this->isEuZone($shopBillingData->getCountryCode())) {
            return false;
        }

        $billingAddress = $order->getBillingAddress();

        if ($billingAddress instanceof VatNumberAddressInterface
            && $billingAddress->hasValidVatNumber()
            && $this->isEuZone($billingAddress->getCountryCode())
            && $billingAddress->getCountryCode() !== $shopBillingData->getCountryCode()) {
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
