<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Rates;

/**
 * VAT rates and countries
 */
interface RatesInterface
{
    public const RATE_STANDARD = 'standard';

    public const RATE_REDUCED = 'reduced';

    /** @return array<string, string> */
    public function getCountries(): array;

    /**
     * @param string $countryCode ISO-3166-1-alpha2 country code
     */
    public function getCountryRate(string $countryCode, string $level = self::RATE_STANDARD): float;
}
