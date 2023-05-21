<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to require a vat number to be valid.
 *
 * @Annotation
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class VatNumber extends Constraint
{
    public string $messageFormat = 'gewebe_sylius_vat_plugin.address.vat_number.invalid_format';

    public string $messageCountry = 'gewebe_sylius_vat_plugin.address.vat_number.invalid_country';

    public string $messageVerified = 'gewebe_sylius_vat_plugin.address.vat_number.not_verified';

    public string $vatNumberPath = 'vatNumber';

    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
