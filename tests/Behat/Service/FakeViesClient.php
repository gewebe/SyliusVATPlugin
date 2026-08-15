<?php

declare(strict_types=1);

namespace Tests\Gewebe\SyliusVATPlugin\Behat\Service;

use Ibericode\Vat\Vies\Client;
use Ibericode\Vat\Vies\ViesException;

/**
 * Replaces the VIES SOAP service in the test environment, so that scenarios do not depend on the live service
 */
final class FakeViesClient extends Client
{
    /**
     * @param string[] $registeredVatNumbers VAT numbers (incl. country prefix) considered as registered
     * @param string[] $unavailableVatNumbers VAT numbers (incl. country prefix) simulating an outage
     */
    public function __construct(
        private readonly array $registeredVatNumbers = [
            'BE0123456789',
            'DE123456789',
            'DE123123123',
            'FR00123456789',
            'HR00123456789',
            'XI123456789',
            'EL123456789',
        ],
        private readonly array $unavailableVatNumbers = ['DE999999999'],
    ) {
        parent::__construct();
    }

    public function getInfo(string $countryCode, string $vatNumber): object
    {
        $fullVatNumber = strtoupper($countryCode . $vatNumber);

        if (in_array($fullVatNumber, $this->unavailableVatNumbers, true)) {
            throw new ViesException('The VIES service is unavailable.');
        }

        return (object) [
            'countryCode' => strtoupper($countryCode),
            'vatNumber' => $vatNumber,
            'valid' => in_array($fullVatNumber, $this->registeredVatNumbers, true),
            'name' => '---',
            'address' => '---',
        ];
    }
}
