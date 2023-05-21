<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number;

interface VatNumberValidatorProviderInterface
{
    public function getValidator(string $countryCode): ?VatNumberValidatorInterface;
}
