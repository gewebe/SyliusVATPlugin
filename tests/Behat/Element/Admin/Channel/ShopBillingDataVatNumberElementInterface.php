<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Element\Admin\Channel;

interface ShopBillingDataVatNumberElementInterface
{
    public function specifyVatNumber(string $vatNumber): void;

    public function hasVatNumber(string $vatNumber): bool;
}
