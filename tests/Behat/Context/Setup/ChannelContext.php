<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Context\Setup;

use Behat\Behat\Context\Context;
use Doctrine\Persistence\ObjectManager;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Core\Model\ChannelInterface;
use Tests\Gewebe\SyliusVATPlugin\Application\src\Entity\Channel\ShopBillingData;

final class ChannelContext implements Context
{
    public function __construct(
        private ObjectManager $channelManager,
    ) {
    }

    /**
     * @Given channel :channel billing data is :company, :street, :postcode :city, :country with :taxId tax ID, :vatNumber VAT No.
     */
    public function channelBillingDataWithVatNumberIs(
        ChannelInterface $channel,
        string $company,
        string $street,
        string $postcode,
        string $city,
        CountryInterface $country,
        string $taxId,
        string $vatNumber,
    ): void {
        $shopBillingData = new ShopBillingData();
        $shopBillingData->setCompany($company);
        $shopBillingData->setStreet($street);
        $shopBillingData->setPostcode($postcode);
        $shopBillingData->setCity($city);
        $shopBillingData->setCountryCode($country->getCode());
        $shopBillingData->setTaxId($taxId);
        $shopBillingData->setVatNumber($vatNumber);

        $channel->setShopBillingData($shopBillingData);

        $this->channelManager->flush();
    }
}
