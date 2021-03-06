<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Page\Admin\Order;

interface UpdatePageInterface
{
    public function specifyBillingAddressVatNumber(string $vatNumber): void;

    public function specifyShippingAddressVatNumber(string $vatNumber): void;
}
