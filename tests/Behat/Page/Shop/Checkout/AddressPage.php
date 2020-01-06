<?php

declare(strict_types=1);

namespace Tests\Gweb\SyliusVATPlugin\Behat\Page\Shop\Checkout;

class AddressPage extends \Sylius\Behat\Page\Shop\Checkout\AddressPage implements AddressPageInterface
{
    /**
     * {@inheritdoc}
     */
    public function specifyShippingAddressVatNumber(string $vatNumber)
    {
        $this->getElement('shipping_vat_number')->setValue($vatNumber);
    }

    /**
     * {@inheritdoc}
     */
    public function specifyBillingAddressVatNumber(string $vatNumber)
    {
        $this->getElement('billing_vat_number')->setValue($vatNumber);
    }

    /**
     * {@inheritdoc}
     */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'shipping_vat_number' => '#sylius_checkout_address_shippingAddress_vatNumber',
            'billing_vat_number' => '#sylius_checkout_address_shippingAddress_vatNumber',
        ]);
    }
}
