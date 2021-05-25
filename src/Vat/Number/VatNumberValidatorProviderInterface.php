<?php

namespace Gewebe\SyliusVATPlugin\Vat\Number;

interface VatNumberValidatorProviderInterface
{
    public function getValidator(string $countryCode): ?VatNumberValidatorInterface;
}
