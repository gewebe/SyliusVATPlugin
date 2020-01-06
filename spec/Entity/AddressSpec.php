<?php

declare(strict_types=1);

namespace spec\Gweb\SyliusVATPlugin\Entity;

use Gweb\SyliusVATPlugin\Entity\Address;
use Gweb\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use PhpSpec\ObjectBehavior;

final class AddressSpec extends ObjectBehavior
{
    function it_is_address()
    {
        $this->shouldHaveType(Address::class);
    }

    function it_implements_address_interface(): void
    {
        $this->shouldImplement(VatNumberAddressInterface::class);
    }

    function it_has_vat_number(): void
    {
        $this->getVatNumber()->shouldReturn(null);
        $this->hasVatNumber()->shouldReturn(false);

        $this->setVatNumber('DE123123123');
        $this->getVatNumber()->shouldReturn('DE123123123');
        $this->hasVatNumber()->shouldReturn(true);
    }

    function it_has_vat_validation_date()
    {
        $now = new \DateTime();

        $this->getVatValidatedAt()->shouldReturn(null);
        $this->setVatValid(true, $now);
        $this->getVatValidatedAt()->shouldReturn($now);
    }

    function it_has_vat_number_validation()
    {
        $this->hasValidVatNumber()->shouldReturn(false);

        $this->setVatValid(true);
        $this->hasValidVatNumber()->shouldReturn(false);

        $this->setVatValid(false);
        $this->setVatNumber('DE123123123');
        $this->hasValidVatNumber()->shouldReturn(false);

        $this->setVatValid(true);
        $this->setVatNumber('DE123123123');
        $this->hasValidVatNumber()->shouldReturn(true);
    }
}
