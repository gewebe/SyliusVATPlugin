<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Shop\Checkout;

class AddressPage extends \Sylius\Behat\Page\Shop\Checkout\AddressPage implements AddressPageInterface
{
    /**
     * @inheritdoc
     */
    public function specifyBillingAddressCompany(string $company)
    {
        $this->getElement('billing_company')->setValue($company);
    }

    /**
     * @inheritdoc
     */
    public function specifyBillingAddressVatNumber(string $vatNumber)
    {
        $this->getElement('billing_vat_number')->setValue($vatNumber);
    }

    /**
     * @inheritdoc
     */
    public function specifyShippingAddressCompany(string $company)
    {
        $this->getElement('shipping_company')->setValue($company);
    }

    /**
     * @inheritdoc
     */
    public function specifyShippingAddressVatNumber(string $vatNumber)
    {
        $this->getElement('shipping_vat_number')->setValue($vatNumber);
    }

    /**
     * @inheritdoc
     */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'billing_company' => '#sylius_checkout_address_billingAddress_company',
            'billing_vat_number' => '#sylius_checkout_address_billingAddress_vatNumber',
            'shipping_company' => '#sylius_checkout_address_shippingAddress_company',
            'shipping_vat_number' => '#sylius_checkout_address_shippingAddress_vatNumber',
        ]);
    }
}
