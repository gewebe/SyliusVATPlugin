<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ShopBillingData as BaseShopBillingData;

/**
 * Example ShopBillingData entity with vat number implemented as trait
 *
 * #@ORM\Entity
 * #@ORM\Table(name="sylius_shop_billing_data")
 */
class ShopBillingData extends BaseShopBillingData implements ShopBillingDataVatNumberInterface
{
    use ShopBillingDataVatNumberAwareTrait;
}
