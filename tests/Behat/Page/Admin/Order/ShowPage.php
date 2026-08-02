<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Admin\Order;

use Sylius\Behat\Page\Shop\Order\ShowPage as BaseShowPage;

class ShowPage extends BaseShowPage implements ShowPageInterface
{
    public function hasBillingVatNumber(string $vatNumber): bool
    {
        return $this->getBillingVatNumber() === $vatNumber;
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
        return $this->getShippingVatNumber() === $vatNumber;
    }

    public function getShippingVatNumber(): string
    {
        return $this->getElement('shipping_vat_number')->getText();
    }

    public function getShippingVatValidation(): string
    {
        return $this->getElement('shipping_vat_validation')->getText();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'billing_vat_number' => '[data-test-billing-address-vat-number]',
            'billing_vat_validation' => '[data-test-billing-address-vat-status]',
            'shipping_vat_number' => '[data-test-shipping-address-vat-number]',
            'shipping_vat_validation' => '[data-test-shipping-address-vat-status]',
        ]);
    }
}
