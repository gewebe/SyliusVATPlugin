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

    public function testSetVatNumberResetsValidState(): void
    {
        $address = new Address();
        $address->setVatNumber('DE123456789');
        $address->setVatValid(true, new \DateTime());

        static::assertTrue($address->hasValidVatNumber());
        static::assertNotNull($address->getVatValidatedAt());

        // Test that setting same number DOES NOT reset state
        $address->setVatNumber('DE123456789');
        static::assertTrue($address->hasValidVatNumber(), 'vatValid should NOT be reset when setting the same vatNumber');
        static::assertNotNull($address->getVatValidatedAt(), 'vatValidatedAt should NOT be reset when setting the same vatNumber');

        // Test that changing number DOES reset state
        $address->setVatNumber('DE987654321');

        static::assertFalse($address->hasValidVatNumber(), 'vatValid should be reset to false after changing vatNumber');
        static::assertNull($address->getVatValidatedAt(), 'vatValidatedAt should be reset to null after changing vatNumber');
    }
}
