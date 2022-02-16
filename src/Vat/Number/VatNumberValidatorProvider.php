<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number;

class VatNumberValidatorProvider implements VatNumberValidatorProviderInterface
{
    /** @var VatNumberValidatorInterface[]|iterable */
    private iterable $validators;

    public function __construct(iterable $validators)
    {
        $this->validators = $validators;
    }

    public function getValidator(string $countryCode): ?VatNumberValidatorInterface
    {
        /** @var VatNumberValidatorInterface $validator */
        foreach ($this->validators as $validator) {
            if (in_array($countryCode, $validator->getCountries(), true)) {
                return $validator;
            }
        }

        return null;
    }
}
