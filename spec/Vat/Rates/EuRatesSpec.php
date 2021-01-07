<?php

declare(strict_types=1);

namespace spec\Gewebe\SyliusVATPlugin\Vat\Rates;

use Gewebe\SyliusVATPlugin\Vat\Rates\RatesInterface;
use Ibericode\Vat\Rates;
use PhpSpec\ObjectBehavior;

class EuRatesSpec extends ObjectBehavior
{
    function let(Rates $rates)
    {
        $rates->getRateForCountry('DE', RatesInterface::RATE_STANDARD)->willReturn(0.19);
        $rates->getRateForCountry('DE', RatesInterface::RATE_REDUCED)->willReturn(0.07);

        $this->beConstructedWith($rates);
    }

    function it_implements_vat_rates_interface(): void
    {
        $this->shouldImplement(RatesInterface::class);
    }

    function it_should_return_eu_countries()
    {
        $this->getCountries()->shouldBeArray();
        $this->getCountries()->shouldHaveKey('DE');
        $this->getCountries()->shouldHaveKey('FR');
        $this->getCountries()->shouldHaveKey('NL');

        $this->getCountries()->shouldNotHaveKey('US');
    }

    function it_should_return_eu_country_vat_rate()
    {
        $this->getCountryRate('DE', RatesInterface::RATE_STANDARD)->shouldReturn(0.19);
        $this->getCountryRate('DE', RatesInterface::RATE_REDUCED)->shouldReturn(0.07);
    }
}
