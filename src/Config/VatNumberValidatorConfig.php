<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Config;

final readonly class VatNumberValidatorConfig
{
    /**
     * @param list<string> $requiredForCountries ISO-3166-1-alpha2 country codes
     */
    public function __construct(
        public bool $isRequired = false,
        public bool $isRequiredForCompany = true,
        public array $requiredForCountries = [],
        public bool $validateFormat = true,
        public bool $validateCountry = true,
        public bool $validateRegistration = true,
        public bool $validateOnServiceUnavailable = false,
        public bool $revalidateOnLogin = true,
        public int $expirationDays = 30,
    ) {
    }
}
