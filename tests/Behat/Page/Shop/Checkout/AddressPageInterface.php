<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Checkout;

interface AddressPageInterface
{
    public function specifyBillingAddressCompany(string $company): void;

    public function specifyBillingAddressVatNumber(string $vatNumber): void;

    public function specifyShippingAddressCompany(string $company): void;

    public function specifyShippingAddressVatNumber(string $vatNumber): void;
}
