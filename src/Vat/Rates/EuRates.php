<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\Vat\Rates;

use Ibericode\Vat\Countries;
use Ibericode\Vat\Rates;

/**
 * European VAT rates and countries
 */
final class EuRates implements RatesInterface
{
    /** @var Rates */
    private $rates;

    /** @var Countries */
    private $countries;

    public function __construct(
        Rates $rates
    ) {
        $this->rates = $rates;
        $this->countries = new Countries();
    }

    /** @inheritDoc */
    public function getCountries(): array
    {
        $euCountries = [];
        foreach ($this->countries as $countryCode => $countryName) {
            if (!$this->countries->isCountryCodeInEU($countryCode)) {
                continue;
            }

            $euCountries[$countryCode] = $countryName;
        }

        return $euCountries;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function getCountryRate(string $countryCode, string $level = self::RATE_STANDARD): float
    {
        return $this->rates->getRateForCountry($countryCode, $level);
    }
}
