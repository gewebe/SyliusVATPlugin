<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Checkout;

interface AddressPageInterface
{
    public function specifyShippingAddressVatNumber(string $vatNumber);

    public function specifyBillingAddressVatNumber(string $vatNumber);
}
