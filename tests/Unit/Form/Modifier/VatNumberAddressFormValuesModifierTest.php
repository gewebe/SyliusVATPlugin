<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Unit\Form\Modifier;

use Gewebe\SyliusVATPlugin\Form\Modifier\VatNumberAddressFormValuesModifier;
use PHPUnit\Framework\TestCase;
use Sylius\Bundle\ShopBundle\Modifier\AddressFormValuesModifierInterface;
use Sylius\Component\Core\Model\Address;
use Tests\Gewebe\SyliusVATPlugin\Entity\Addressing\Address as VatNumberAddress;

final class VatNumberAddressFormValuesModifierTest extends TestCase
{
    private VatNumberAddressFormValuesModifier $modifier;

    protected function setUp(): void
    {
        if (!interface_exists(AddressFormValuesModifierInterface::class)) {
            self::markTestSkipped('Address form value modifiers require Sylius 2.2 or newer.');
        }

        $this->modifier = new VatNumberAddressFormValuesModifier();
    }

    public function testItIsAnAddressFormValuesModifier(): void
    {
        self::assertInstanceOf(AddressFormValuesModifierInterface::class, $this->modifier);
    }

    public function testItAddsTheVatNumberWithoutChangingExistingFormValues(): void
    {
        $address = new VatNumberAddress();
        $address->setVatNumber('DE123456789');

        self::assertSame(
            ['firstName' => 'Ada', 'vatNumber' => 'DE123456789'],
            $this->modifier->modify(['firstName' => 'Ada'], $address),
        );
    }

    public function testItAddsANullVatNumber(): void
    {
        self::assertSame(
            ['vatNumber' => null],
            $this->modifier->modify([], new VatNumberAddress()),
        );
    }

    public function testItIgnoresAddressesWithoutVatNumberSupport(): void
    {
        self::assertSame(
            ['firstName' => 'Ada'],
            $this->modifier->modify(['firstName' => 'Ada'], new Address()),
        );
    }
}
