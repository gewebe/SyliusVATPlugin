<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Ui\Admin;

use Behat\Behat\Context\Context;
use Tests\Gewebe\SyliusVATPlugin\Behat\Element\Admin\Channel\ShopBillingDataVatNumberElementInterface;
use Webmozart\Assert\Assert;

final class ManagingChannelsVatContext implements Context
{
    public function __construct(private ShopBillingDataVatNumberElementInterface $shopBillingDataElement)
    {
    }

    /**
     * @When I specify VAT number as :vatNumber
     */
    public function specifyShopBillingVatNumberAs(string $vatNumber): void
    {
        $this->shopBillingDataElement->specifyVatNumber($vatNumber);
    }

    /**
     * @Then this channel VAT number should be :vatNumber
     */
    public function thisChannelShopBillingVatNumberShouldBe(string $vatNumber): void
    {
        Assert::true($this->shopBillingDataElement->hasVatNumber($vatNumber));
    }
}
