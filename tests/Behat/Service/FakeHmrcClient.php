<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Service;

use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\Hmrc\HmrcClientInterface;

/**
 * Replaces the HMRC API in the test environment so that scenarios do not depend on the live service
 */
final class FakeHmrcClient implements HmrcClientInterface
{
    /**
     * @param string[] $registeredVatNumbers VAT registration numbers considered as registered
     * @param string[] $unavailableVatNumbers VAT registration numbers simulating an outage
     */
    public function __construct(
        private readonly array $registeredVatNumbers = ['123456789'],
        private readonly array $unavailableVatNumbers = ['999999999'],
    ) {
    }

    public function checkVatNumber(string $vatRegistrationNumber): bool
    {
        if (in_array($vatRegistrationNumber, $this->unavailableVatNumbers, true)) {
            throw new ClientException('The HMRC service is unavailable.');
        }

        return in_array($vatRegistrationNumber, $this->registeredVatNumbers, true);
    }
}
