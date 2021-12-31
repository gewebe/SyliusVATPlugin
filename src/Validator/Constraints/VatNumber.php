<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to require a vat number to be valid.
 *
 * @Annotation
 * @psalm-suppress PossiblyUndefinedMethod
 */
class VatNumber extends Constraint
{
    /** @var string */
    public $messageFormat = 'gewebe_sylius_vat_plugin.address.vat_number.invalid_format';

    /** @var string */
    public $messageCountry = 'gewebe_sylius_vat_plugin.address.vat_number.invalid_country';

    /** @var string */
    public $messageVerified = 'gewebe_sylius_vat_plugin.address.vat_number.not_verified';

    /** @var string */
    public $vatNumberPath = 'vatNumber';

    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
