<?php

declare(strict_types=1);

namespace spec\Gewebe\SyliusVATPlugin\Entity;

use Gewebe\SyliusVATPlugin\Entity\ShopBillingData;
use Gewebe\SyliusVATPlugin\Entity\ShopBillingDataVatNumberInterface;
use PhpSpec\ObjectBehavior;

final class ShopBillingDataSpec extends ObjectBehavior
{
    function it_is_address()
    {
        $this->shouldHaveType(ShopBillingData::class);
    }

    function it_implements_address_interface(): void
    {
        $this->shouldImplement(ShopBillingDataVatNumberInterface::class);
    }

    function it_has_vat_number(): void
    {
        $this->getVatNumber()->shouldReturn(null);
        $this->hasVatNumber()->shouldReturn(false);

        $this->setVatNumber('ATU12345678');
        $this->getVatNumber()->shouldReturn('ATU12345678');
        $this->hasVatNumber()->shouldReturn(true);
    }
}
