<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Entity;

use DateTime;
use Sylius\Component\Core\Model\AddressInterface;

/**
 * Address model with vat number
 */
interface VatNumberAddressInterface extends AddressInterface
{
    public function getVatNumber(): ?string;

    public function setVatNumber(?string $vatNumber): void;

    public function hasVatNumber(): bool;

    public function hasValidVatNumber(): bool;

    public function setVatValid(bool $valid, ?DateTime $validatedAt = null): void;

    public function getVatValidatedAt(): ?DateTime;
}
