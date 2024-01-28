<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Element\Admin\Channel;

use FriendsOfBehat\PageObjectExtension\Element\Element;

final class ShopBillingDataVatNumberElement extends Element implements ShopBillingDataVatNumberElementInterface
{
    public function specifyVatNumber(string $vatNumber): void
    {
        $this->getElement('vat_number')->setValue($vatNumber);
    }

    public function hasVatNumber(string $vatNumber): bool
    {
        return $vatNumber === $this->getElement('vat_number')->getValue();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(parent::getDefinedElements(), [
            'vat_number' => '#sylius_channel_shopBillingData_vatNumber',
        ]);
    }
}
