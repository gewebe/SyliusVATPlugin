<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Unit\Vat\Rates;

use Gewebe\SyliusVATPlugin\Vat\Rates\EuRates;
use Gewebe\SyliusVATPlugin\Vat\Rates\RatesInterface;
use Ibericode\Vat\Rates;
use PHPUnit\Framework\TestCase;

final class EuRatesTest extends TestCase
{
    private EuRates $euRates;

    protected function setUp(): void
    {
        $rates = $this->createMock(Rates::class);

        $rates->method('getRateForCountry')
            ->willReturnMap([
                ['DE', RatesInterface::RATE_STANDARD, 0.19],
                ['DE', RatesInterface::RATE_REDUCED, 0.07],
            ]);

        $this->euRates = new EuRates($rates);
    }

    public function testIsRates(): void
    {
        self::assertInstanceOf(RatesInterface::class, $this->euRates);
    }

    public function testGetCountries(): void
    {
        $countries = $this->euRates->getCountries();

        self::assertIsArray($countries);
        self::assertArrayHasKey('DE', $countries);
        self::assertArrayHasKey('FR', $countries);
        self::assertArrayHasKey('NL', $countries);
        self::assertArrayNotHasKey('US', $countries);
    }

    public function testGetCountryRate(): void
    {
        $standardRate = $this->euRates->getCountryRate('DE', RatesInterface::RATE_STANDARD);
        $reducedRate = $this->euRates->getCountryRate('DE', RatesInterface::RATE_REDUCED);

        self::assertSame(0.19, $standardRate);
        self::assertSame(0.07, $reducedRate);
    }
}
