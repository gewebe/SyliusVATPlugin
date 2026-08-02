<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Rates;

use Ibericode\Vat\Countries;
use Ibericode\Vat\Rates;

/**
 * European VAT rates and countries
 */
final class EuRates implements RatesInterface
{
    public function __construct(
        private readonly Rates $rates,
        private readonly Countries $countries = new Countries(),
    ) {
    }

    /**
     * @return array<string, string>
     */
    public function getCountries(): array
    {
        $euCountries = [];

        /**
         * @var string $countryCode
         * @var string $countryName
         */
        foreach ($this->countries as $countryCode => $countryName) {
            if (!$this->countries->isCountryCodeInEU($countryCode)) {
                continue;
            }

            $euCountries[$countryCode] = $countryName;
        }

        return $euCountries;
    }

    public function getCountryRate(string $countryCode, string $level = self::RATE_STANDARD): float
    {
        return $this->rates->getRateForCountry($countryCode, $level);
    }
}
