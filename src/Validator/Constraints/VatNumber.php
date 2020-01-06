<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Constraint to require a vat number to be valid.
 *
 * @Annotation
 */
class VatNumber extends Constraint
{
    /** @var string */
    public $messageFormat = 'gweb_sylius_vat.address.vat_number.invalid_format';

    /** @var string */
    public $messageCountry = 'gweb_sylius_vat.address.vat_number.invalid_country';

    /** @var string */
    public $messageVerified = 'gweb_sylius_vat.address.vat_number.not_verified';

    /** @var string */
    public $vatNumberPath = 'vatNumber';

    /** {@inheritdoc} */
    public function getTargets()
    {
        return [self::CLASS_CONSTRAINT, self::PROPERTY_CONSTRAINT];
    }
}
