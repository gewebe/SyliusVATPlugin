<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Vat\Number\Hmrc;

use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;

/**
 * Client for the HMRC "Check a UK VAT number" API
 *
 * @see https://developer.service.hmrc.gov.uk/api-documentation/docs/api/service/vat-registered-companies-api
 */
interface HmrcClientInterface
{
    /**
     * Check whether a UK VAT registration number is registered
     *
     * @param string $vatRegistrationNumber 9 digit VAT registration number without country prefix
     *
     * @throws ClientException if the HMRC service is unavailable
     */
    public function checkVatNumber(string $vatRegistrationNumber): bool;
}
