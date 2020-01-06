<?php

declare(strict_types=1);

namespace Tests\Gweb\SyliusVATPlugin\Behat\Page\Shop\Checkout;

interface AddressPageInterface
{
    /**
     * @param string $vatNumber
     */
    public function specifyShippingAddressVatNumber(string $vatNumber);

    /**
     * @param string $vatNumber
     */
    public function specifyBillingAddressVatNumber(string $vatNumber);
}
