<?php

declare(strict_types=1);

namespace Gweb\SyliusVATPlugin\Command;

use Gweb\SyliusVATPlugin\Vat\Rates\RatesInterface;
use Sylius\Component\Addressing\Factory\ZoneFactory;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\TaxRateInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Install EU countries, zones and VAT rates to Sylius
 */
final class EuInstallCommand extends Command
{
    /** @var RatesInterface */
    private $vatRates;

    /** @var FactoryInterface */
    private $countryFactory;

    /** @var RepositoryInterface */
    private $countryRepository;

    /** @var ZoneFactory */
    private $zoneFactory;

    /** @var RepositoryInterface */
    private $zoneRepository;

    /** @var FactoryInterface */
    private $taxRateFactory;

    /** @var RepositoryInterface */
    private $taxRateRepository;

    /** @var FactoryInterface */
    private $taxCategoryFactory;

    /** @var RepositoryInterface */
    private $taxCategoryRepository;

    public function __construct(
        RatesInterface $vatRates,
        FactoryInterface $countryFactory,
        RepositoryInterface $countryRepository,
        ZoneFactory $zoneFactory,
        RepositoryInterface $zoneRepository,
        FactoryInterface $taxRateFactory,
        RepositoryInterface $taxRateRepository,
        FactoryInterface $taxCategoryFactory,
        RepositoryInterface $taxCategoryRepository
    ) {
        $this->vatRates = $vatRates;
        $this->countryFactory = $countryFactory;
        $this->countryRepository = $countryRepository;
        $this->zoneFactory = $zoneFactory;
        $this->zoneRepository = $zoneRepository;
        $this->taxRateFactory = $taxRateFactory;
        $this->taxRateRepository = $taxRateRepository;
        $this->taxCategoryFactory = $taxCategoryFactory;
        $this->taxCategoryRepository = $taxCategoryRepository;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setName('vat:install:eu')
            ->setDescription('Install European countries, zones and VAT rates')
            ->addArgument(
                'country',
                InputArgument::OPTIONAL,
                'Domestic Country'
            )
            ->addOption(
                'categories',
                'c',
                InputOption::VALUE_REQUIRED,
                'Tax categories, e.g.: standard,reduced',
                'standard'
            )
            ->addOption(
                'included',
                'i',
                InputOption::VALUE_NONE,
                'Tax rate is included in price'
            )
            ->addOption(
                'threshold',
                't',
                InputOption::VALUE_REQUIRED,
                'Threshold Countries',
                ''
            );
    }

    private function getArgumentCountry(InputInterface $input): string
    {
        $country = $input->getArgument('country');
        if (!is_string($country)) {
            $country = '';
        }

        return strtolower($country);
    }

    private function getOptionCategories(InputInterface $input): array
    {
        $categories = $input->getOption('categories');
        if (!is_string($categories)) {
            $categories = '';
        }

        return explode(',', $categories);
    }

    private function getOptionIncluded(InputInterface $input): bool
    {
        return $input->getOption('included') === true;
    }

    private function getOptionThreshold(InputInterface $input): array
    {
        $threshold = $input->getOption('threshold');
        if (!is_string($threshold)) {
            $threshold = '';
        }

        return explode(',', strtolower($threshold));
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $baseCountry = $this->getArgumentCountry($input);

        $thresholdCountries = $this->getOptionThreshold($input);

        $taxCategories = $this->getOptionCategories($input);
        foreach ($taxCategories as $taxCategory) {
            $this->addTaxCategory($taxCategory);
        }

        $euZones = [];
        foreach ($this->vatRates->getCountries() as $countryCode => $countryName) {
            $output->writeln('Install: '.$countryCode);

            $country = $this->addCountry($countryCode);

            $zone = $this->addZone(
                $countryCode,
                $countryName,
                [$country->getCode()],
                ZoneInterface::TYPE_COUNTRY
            );

            $euZones[] = $zone->getCode();

            if (strlen($baseCountry) > 0
                && !in_array(strtolower($countryCode), $thresholdCountries, true)) {
                continue;
            }

            foreach ($taxCategories as $taxCategory) {
                $this->addTaxRate(
                    $countryCode,
                    $countryCode,
                    $taxCategory,
                    $zone,
                    $this->getOptionIncluded($input)
                );
            }
        }

        $output->writeln('Install: EU');
        $zone = $this->addZone(
            'EU',
            'European Union',
            $euZones,
            ZoneInterface::TYPE_ZONE
        );

        if (strlen($baseCountry) > 0) {
            foreach ($taxCategories as $taxCategory) {
                $this->addTaxRate(
                    $baseCountry,
                    'eu',
                    $taxCategory,
                    $zone,
                    $this->getOptionIncluded($input)
                );
            }
        }

        return 0;
    }

    private function addCountry(string $code): CountryInterface
    {
        $country = $this->countryRepository->findOneBy(['code' => strtoupper($code)]);
        if ($country instanceof CountryInterface) {
            return $country;
        }

        /** @var CountryInterface $country */
        $country = $this->countryFactory->createNew();
        $country->setCode(strtoupper($code));

        $this->countryRepository->add($country);

        return $country;
    }

    private function addZone(string $code, string $name, array $countries, string $type): ZoneInterface
    {
        $zone = $this->zoneRepository->findOneBy(['code' => strtoupper($code), 'type' => $type]);
        if ($zone instanceof ZoneInterface) {
            return $zone;
        }

        /** @var ZoneInterface $zone */
        $zone = $this->zoneFactory->createWithMembers($countries);
        $zone->setCode(strtoupper($code));
        $zone->setName($name);
        $zone->setType($type);

        $this->zoneRepository->add($zone);

        return $zone;
    }

    private function addTaxCategory(string $name): TaxCategoryInterface
    {
        $taxCategory = $this->taxCategoryRepository->findOneBy(['code' => strtolower($name)]);
        if ($taxCategory instanceof TaxCategoryInterface) {
            return $taxCategory;
        }

        /** @var TaxCategoryInterface $taxCategory */
        $taxCategory = $this->taxCategoryFactory->createNew();
        $taxCategory->setCode(strtolower($name));
        $taxCategory->setName(ucfirst($name));

        $this->taxCategoryRepository->add($taxCategory);

        return $taxCategory;
    }

    private function addTaxRate(
        string $country,
        string $code,
        string $category,
        ZoneInterface $zone,
        bool $includedInPrice = false
    ): void {
        try {
            $countryRate = $this->vatRates->getCountryRate(strtoupper($country), $category) / 100;
        } catch (\Exception $e) {
            return;
        }

        $taxRate = $this->taxRateRepository->findOneBy(['code' => strtolower($code).'-'.$category]);
        if ($taxRate instanceof TaxRateInterface) {
            if ($taxRate->getAmount() !== $countryRate) {
                $taxRate->setAmount($countryRate);

                $this->taxRateRepository->add($taxRate);
            }
            return;
        }

        /** @var TaxCategoryInterface $taxCategory */
        $taxCategory = $this->taxCategoryRepository->findOneBy(['code' => $category]);

        /** @var TaxRateInterface $taxRate */
        $taxRate = $this->taxRateFactory->createNew();
        $taxRate->setCode(strtolower($code.'-'.$category));
        $taxRate->setName('VAT');
        $taxRate->setAmount($countryRate);
        $taxRate->setCalculator('default');
        $taxRate->setIncludedInPrice($includedInPrice);
        $taxRate->setZone($zone);
        $taxRate->setCategory($taxCategory);

        $this->taxRateRepository->add($taxRate);
    }
}
