<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Command;

use Exception;
use Gewebe\SyliusVATPlugin\Vat\Rates\RatesInterface;
use Sylius\Component\Addressing\Factory\ZoneFactory;
use Sylius\Component\Addressing\Model\CountryInterface;
use Sylius\Component\Addressing\Model\ZoneInterface;
use Sylius\Component\Core\Model\Scope;
use Sylius\Component\Core\Model\TaxRateInterface;
use Sylius\Component\Taxation\Model\TaxCategoryInterface;
use Sylius\Resource\Doctrine\Persistence\RepositoryInterface;
use Sylius\Resource\Factory\FactoryInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Install EU countries, zones and VAT rates to Sylius
 *
 *  @Todo: rewrite setup of different tax schemas
 */
#[AsCommand(
    name: 'vat:install:eu',
    description: 'Install European countries, zones and VAT rates',
)]
final class EuInstallCommand extends Command
{
    /**
     * @param FactoryInterface<CountryInterface> $countryFactory
     * @param RepositoryInterface<CountryInterface> $countryRepository
     * @param RepositoryInterface<ZoneInterface> $zoneRepository
     * @param FactoryInterface<TaxRateInterface> $taxRateFactory
     * @param RepositoryInterface<TaxRateInterface> $taxRateRepository
     * @param FactoryInterface<TaxCategoryInterface> $taxCategoryFactory
     * @param RepositoryInterface<TaxCategoryInterface> $taxCategoryRepository
     */
    public function __construct(
        private readonly RatesInterface $vatRates,
        private readonly FactoryInterface $countryFactory,
        private readonly RepositoryInterface $countryRepository,
        private readonly ZoneFactory $zoneFactory,
        private readonly RepositoryInterface $zoneRepository,
        private readonly FactoryInterface $taxRateFactory,
        private readonly RepositoryInterface $taxRateRepository,
        private readonly FactoryInterface $taxCategoryFactory,
        private readonly RepositoryInterface $taxCategoryRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'country',
                InputArgument::OPTIONAL,
                'Domestic Country',
            )
            ->addOption(
                'categories',
                'c',
                InputOption::VALUE_REQUIRED,
                'Tax categories, e.g.: standard,reduced',
                'standard',
            )
            ->addOption(
                'included',
                'i',
                InputOption::VALUE_NONE,
                'Tax rate is included in price',
            )
            ->addOption(
                'threshold',
                't',
                InputOption::VALUE_REQUIRED,
                'Threshold Countries',
                '',
            )
        ;
    }

    private function getArgumentCountry(InputInterface $input): string
    {
        /** @var string $country */
        $country = $input->getArgument('country') ?? '';

        return strtolower($country);
    }

    /**
     * @return string[]
     */
    private function getOptionCategories(InputInterface $input): array
    {
        /** @var string $categories */
        $categories = $input->getOption('categories') ?? '';

        return explode(',', $categories);
    }

    private function getOptionIncluded(InputInterface $input): bool
    {
        return $input->getOption('included') === true;
    }

    private function getOptionThreshold(InputInterface $input): array
    {
        /** @var string $threshold */
        $threshold = $input->getOption('threshold') ?? '';

        return explode(',', strtolower($threshold));
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $baseCountry = $this->getArgumentCountry($input);

        $thresholdCountries = $this->getOptionThreshold($input);

        $taxCategories = $this->getOptionCategories($input);
        foreach ($taxCategories as $taxCategory) {
            $this->addTaxCategory($taxCategory);
        }

        $euZones = [];
        foreach ($this->vatRates->getCountries() as $countryCode => $countryName) {
            $output->writeln('Install: ' . $countryCode);

            $country = $this->addCountry(strtoupper($countryCode));

            $zone = $this->addZone(
                strtoupper($countryCode) . '-vat',
                $countryName . ' VAT',
                [(string) $country->getCode()],
                ZoneInterface::TYPE_COUNTRY,
            );

            $euZones[] = (string) $zone->getCode();

            if (in_array(strtolower($countryCode), $thresholdCountries, true)) {
                $zone = $this->addZone(
                    $countryCode . '-tax',
                    $countryName . ' Tax',
                    [(string) $country->getCode()],
                    ZoneInterface::TYPE_COUNTRY,
                    Scope::TAX,
                );
            } elseif ('' !== $baseCountry) {
                continue;
            }

            foreach ($taxCategories as $taxCategory) {
                $this->addTaxRate(
                    $countryCode,
                    $countryCode,
                    $taxCategory,
                    $zone,
                    $this->getOptionIncluded($input),
                );
            }
        }

        $output->writeln('Install: EU');
        $zone = $this->addZone(
            'EU',
            'European Union VAT',
            $euZones,
            ZoneInterface::TYPE_ZONE,
        );

        if ('' !== $baseCountry) {
            foreach ($taxCategories as $taxCategory) {
                $this->addTaxRate(
                    $baseCountry,
                    'eu',
                    $taxCategory,
                    $zone,
                    $this->getOptionIncluded($input),
                );
            }
        }

        return Command::SUCCESS;
    }

    private function addCountry(string $code): CountryInterface
    {
        /** @var CountryInterface|null $country */
        $country = $this->countryRepository->findOneBy(['code' => $code]);
        if ($country instanceof CountryInterface) {
            return $country;
        }

        /** @var CountryInterface $country */
        $country = $this->countryFactory->createNew();
        $country->setCode($code);

        $this->countryRepository->add($country);

        return $country;
    }

    private function addZone(string $code, string $name, array $countries, string $type, string $scope = Scope::ALL): ZoneInterface
    {
        /** @var ZoneInterface|null $zone */
        $zone = $this->zoneRepository->findOneBy(['code' => $code, 'type' => $type]);
        if ($zone instanceof ZoneInterface) {
            return $zone;
        }

        $zone = $this->zoneFactory->createWithMembers($countries);
        $zone->setCode($code);
        $zone->setName($name);
        $zone->setType($type);
        $zone->setScope($scope);

        $this->zoneRepository->add($zone);

        return $zone;
    }

    private function addTaxCategory(string $name): TaxCategoryInterface
    {
        /** @var TaxCategoryInterface|null $taxCategory */
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
        bool $includedInPrice = false,
    ): void {
        try {
            $countryRate = $this->vatRates->getCountryRate(strtoupper($country), $category) / 100;
        } catch (Exception) {
            return;
        }

        /** @var TaxRateInterface|null $taxRate */
        $taxRate = $this->taxRateRepository->findOneBy(['code' => strtolower($code) . '-' . $category]);
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
        $taxRate->setCode(strtolower($code . '-' . $category));
        $taxRate->setName('VAT');
        $taxRate->setAmount($countryRate);
        $taxRate->setCalculator('default');
        $taxRate->setIncludedInPrice($includedInPrice);
        $taxRate->setZone($zone);
        $taxRate->setCategory($taxCategory);

        $this->taxRateRepository->add($taxRate);
    }
}
