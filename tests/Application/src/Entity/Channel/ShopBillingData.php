<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Application\src\Entity\Channel;

use Doctrine\ORM\Mapping as ORM;
use Gewebe\SyliusVATPlugin\Entity\ShopBillingDataVatNumberAwareTrait;
use Gewebe\SyliusVATPlugin\Entity\ShopBillingDataVatNumberInterface;
use Sylius\Component\Core\Model\ShopBillingData as BaseShopBillingData;

/**
 * ShopBillingData entity with vat number implemented as trait
 *
 * @ORM\Entity
 * @ORM\Table(name="sylius_shop_billing_data")
 */
class ShopBillingData extends BaseShopBillingData implements ShopBillingDataVatNumberInterface
{
    use ShopBillingDataVatNumberAwareTrait;
}
