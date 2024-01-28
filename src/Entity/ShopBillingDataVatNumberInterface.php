<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Entity;

use Sylius\Component\Core\Model\ShopBillingDataInterface;

/**
 * ShopBillingData model with vat number
 */
interface ShopBillingDataVatNumberInterface extends ShopBillingDataInterface
{
    public function getVatNumber(): ?string;

    public function setVatNumber(?string $vatNumber): void;

    public function hasVatNumber(): bool;
}
