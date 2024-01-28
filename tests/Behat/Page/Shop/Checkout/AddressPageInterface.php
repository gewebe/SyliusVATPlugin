<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Checkout;

interface AddressPageInterface
{
    public function specifyBillingAddressCompany(string $company);

    public function specifyBillingAddressVatNumber(string $vatNumber);

    public function specifyShippingAddressCompany(string $company);

    public function specifyShippingAddressVatNumber(string $vatNumber);
}
