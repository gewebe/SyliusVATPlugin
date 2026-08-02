<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Unit\Entity;

use Gewebe\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use PHPUnit\Framework\TestCase;
use Tests\Gewebe\SyliusVATPlugin\Entity\Addressing\Address;

final class AddressTest extends TestCase
{
    public function testIsVatNumberAddress(): void
    {
        $address = new Address();
        static::assertInstanceOf(VatNumberAddressInterface::class, $address);
    }

    public function testHasVatNumber(): void
    {
        $address = new Address();
        static::assertFalse($address->hasVatNumber());

        $address->setVatNumber(null);
        static::assertFalse($address->hasVatNumber());

        $address->setVatNumber('');
        static::assertFalse($address->hasVatNumber());

        $address->setVatNumber(' ');
        static::assertTrue($address->hasVatNumber());

        $address->setVatNumber('DE123123123');
        static::assertTrue($address->hasVatNumber());
    }

    public function testGetVatValidatedAt(): void
    {
        $address = new Address();
        $now = new \DateTime();

        static::assertNull($address->getVatValidatedAt());

        $address->setVatValid(true, $now);

        static::assertSame($now, $address->getVatValidatedAt());
    }

    public function testHasValidVatNumber(): void
    {
        $address = new Address();

        static::assertFalse($address->hasValidVatNumber());

        $address->setVatValid(true);
        static::assertFalse($address->hasValidVatNumber());

        $address->setVatValid(false);
        $address->setVatNumber('DE123123123');
        static::assertFalse($address->hasValidVatNumber());

        $address->setVatValid(true);
        static::assertTrue($address->hasValidVatNumber());
    }
}
