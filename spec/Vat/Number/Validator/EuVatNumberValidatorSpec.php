<?php

declare(strict_types=1);

namespace spec\Gewebe\SyliusVATPlugin\Vat\Number\Validator;

use Gewebe\SyliusVATPlugin\Vat\Number\ClientException;
use Gewebe\SyliusVATPlugin\Vat\Number\VatNumberValidatorInterface;
use Ibericode\Vat\Validator;
use Ibericode\Vat\Vies\ViesException;
use PhpSpec\ObjectBehavior;

class EuVatNumberValidatorSpec extends ObjectBehavior
{
    function let(Validator $validator)
    {
        $validator->validateVatNumberFormat('XY123')->willReturn(false);
        $validator->validateVatNumberFormat('DE123123123')->willReturn(true);
        $validator->validateVatNumber('DE123123123')->willReturn(true);
        $validator->validateVatNumber('DE321321321')->willThrow(ViesException::class);

        $this->beConstructedWith($validator);
    }

    function it_implements_vat_validator_interface(): void
    {
        $this->shouldImplement(VatNumberValidatorInterface::class);
    }

    function it_validate_vat_number_country()
    {
        $this->validateCountry('DE123123123', 'DE')->shouldReturn(true);
        $this->validateCountry('DE123123123', 'de')->shouldReturn(true);
        $this->validateCountry('DE123123123', 'FR')->shouldReturn(false);
    }

    function it_validate_vat_number_format()
    {
        $this->validateFormat('XY123')->shouldReturn(false);
        $this->validateFormat('DE123123123')->shouldReturn(true);
    }

    function it_validate_vat_number()
    {
        $this->validate('DE123123123')->shouldReturn(true);
    }

    function it_should_throw_client_exception_if_validation_service_is_down()
    {
        $this->shouldThrow(ClientException::class)->during('validate', ['DE321321321']);
    }
}
