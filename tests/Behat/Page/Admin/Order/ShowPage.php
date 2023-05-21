<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Admin\Order;

use Sylius\Behat\Page\Shop\Order\ShowPage as BaseShowPage;

class ShowPage extends BaseShowPage implements ShowPageInterface
{
    public function hasBillingVatNumber(string $vatNumber): bool
    {
        if ($this->getBillingVatNumber() == $vatNumber) {
            return true;
        }

        return false;
    }

    public function getBillingVatNumber(): string
    {
        return $this->getElement('billing_vat_number')->getText();
    }

    public function getBillingVatValidation(): string
    {
        return $this->getElement('billing_vat_validation')->getText();
    }

    public function hasShippingVatNumber(string $vatNumber): bool
    {
        if ($this->getShippingVatNumber() == $vatNumber) {
            return true;
        }

        return false;
    }

    public function getShippingVatNumber(): string
    {
        return $this->getElement('shipping_vat_number')->getText();
    }

    public function getShippingVatValidation(): string
    {
        return $this->getElement('shipping_vat_validation')->getText();
    }

    /** @inheritdoc */
    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'billing_vat_number' => '#billing-address div.address-vat-number span',
            'billing_vat_validation' => '#billing-address div.address-vat-status',
            'shipping_vat_number' => '#shipping-address div.address-vat-number span',
            'shipping_vat_validation' => '#shipping-address div.address-vat-status',
        ]);
    }
}
