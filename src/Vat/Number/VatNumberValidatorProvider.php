<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number;

class VatNumberValidatorProvider implements VatNumberValidatorProviderInterface
{
    /**
     * @param iterable<VatNumberValidatorInterface> $validators
     */
    public function __construct(private readonly iterable $validators)
    {
    }

    public function getValidator(string $countryCode): ?VatNumberValidatorInterface
    {
        foreach ($this->validators as $validator) {
            if (in_array($countryCode, $validator->getCountries(), true)) {
                return $validator;
            }
        }

        return null;
    }
}
