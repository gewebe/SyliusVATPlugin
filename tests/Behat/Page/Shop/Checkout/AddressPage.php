<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Checkout;

use Sylius\Behat\Page\Shop\Checkout\AddressPage as BaseAddressPage;

class AddressPage extends BaseAddressPage implements AddressPageInterface
{
    public function specifyBillingAddressCompany(string $company): void
    {
        $this->getElement('billing_company')->setValue($company);
    }

    public function specifyBillingAddressVatNumber(string $vatNumber): void
    {
        $this->getElement('billing_vat_number')->setValue($vatNumber);
    }

    public function specifyShippingAddressCompany(string $company): void
    {
        $this->getElement('shipping_company')->setValue($company);
    }

    public function specifyShippingAddressVatNumber(string $vatNumber): void
    {
        $this->getElement('shipping_vat_number')->setValue($vatNumber);
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'billing_company' => '#sylius_shop_checkout_address_billingAddress_company',
            'billing_vat_number' => '#sylius_shop_checkout_address_billingAddress_vatNumber',
            'shipping_company' => '#sylius_shop_checkout_address_shippingAddress_company',
            'shipping_vat_number' => '#sylius_shop_checkout_address_shippingAddress_vatNumber',
        ]);
    }
}
