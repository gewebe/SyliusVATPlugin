<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to require a vat number to be valid.
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_PROPERTY)]
class VatNumber extends Constraint
{
    public string $messageRequired = 'gewebe_sylius_vat_plugin.address.vat_number.required';

    public string $messageRequiredForCompany = 'gewebe_sylius_vat_plugin.address.vat_number.required_for_company';

    public string $messageInvalidFormat = 'gewebe_sylius_vat_plugin.address.vat_number.invalid_format';

    public string $messageInvalidCountry = 'gewebe_sylius_vat_plugin.address.vat_number.invalid_country';

    public string $messageInvalidRegistration = 'gewebe_sylius_vat_plugin.address.vat_number.invalid_registration';

    public string $messageServiceUnavailable = 'gewebe_sylius_vat_plugin.address.vat_number.service_unavailable';

    public string $vatNumberPath = 'vatNumber';

    public function getTargets(): array|string
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
