<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Admin\Order;

use Sylius\Behat\Page\Admin\Order\UpdatePage as BaseUpdatePage;

class UpdatePage extends BaseUpdatePage implements UpdatePageInterface
{
    public function specifyBillingAddressVatNumber(string $vatNumber): void
    {
        $this->getElement('billing_vat_number')->setValue($vatNumber);
    }

    public function specifyShippingAddressVatNumber(string $vatNumber): void
    {
        $this->getElement('shipping_vat_number')->setValue($vatNumber);
    }

    /** @inheritdoc */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'billing_vat_number' => '#sylius_order_billingAddress_vatNumber',
            'shipping_vat_number' => '#sylius_order_shippingAddress_vatNumber',
        ]);
    }
}
