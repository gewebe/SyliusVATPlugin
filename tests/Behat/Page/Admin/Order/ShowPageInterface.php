<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Admin\Order;

interface ShowPageInterface
{
    public function hasBillingVatNumber(string $vatNumber): bool;

    public function getBillingVatNumber(): string;

    public function getBillingVatValidation(): string;

    public function hasShippingVatNumber(string $vatNumber): bool;

    public function getShippingVatNumber(): string;

    public function getShippingVatValidation(): string;
}
