
# Sylius VAT number and calculation plugin

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-travis]][link-travis]
[![Quality Score][ico-code-quality]][link-code-quality]

## Features
 * Installer for EU countries, zones and VAT rates configuration
 * VAT number field at `Address` entity
 * Validate VAT numbers (by country, format and existence)
 * Placing an order with 0% VAT if
    * Customers billing country is different from shop billing data
    * VAT number validation was successful

## Installation

### Download the plugin via composer
```bash
composer require gweb/sylius-vat-plugin
```

### Enable the plugin
Register the plugin by adding it to your `config/bundles.php` file

```php
<?php

return [
    // ...
    Gweb\SyliusVATPlugin\GwebSyliusVATPlugin::class => ['all' => true],
];
```

### Configure the plugin

```yaml
# config/packages/gweb_sylius_vat.yaml

imports:
    - { resource: '@GwebSyliusVATPlugin/Resources/config/app/config.yml'}
```

### Copy templates

Copy customized templates to your templates directory (e.g `templates/bundles/`):

```bash
mkdir -p templates/bundles/SyliusAdminBundle/
cp -R vendor/gweb/sylius-vat-plugin/src/Resources/views/SyliusAdminBundle/* templates/bundles/SyliusAdminBundle/
mkdir -p templates/bundles/SyliusShopBundle/
cp -R vendor/gweb/sylius-vat-plugin/src/Resources/views/SyliusShopBundle/* templates/bundles/SyliusShopBundle/
```

### Extend `Address` entity

- If you use `annotations` mapping:

```php
# src/Entity/Addressing/Address.php

namespace App\Entity\Addressing;

use Doctrine\ORM\Mapping as ORM;
use Gweb\SyliusVATPlugin\Entity\VatNumberAddressInterface;
use Gweb\SyliusVATPlugin\Entity\VatNumberAwareTrait;
use Sylius\Component\Core\Model\Address as BaseAddress;

/**
 * @ORM\Entity
 * @ORM\Table(name="sylius_address")
 */
class Address extends BaseAddress implements VatNumberAddressInterface
{
    use VatNumberAwareTrait;
```

- If you use `yaml` mapping add also:

```yaml
# config/doctrine/Address.orm.yaml

App\Entity\Addressing\Address:
    type: entity
    table: sylius_address
    fields:
        vatNumber:
            type: string
            column: vat_number
            nullable: true
        vatValid:
            type: boolean
            column: vat_valid
            nullable: true
            options:
                unsigned: true
                default: 0
        vatValidatedAt:
            type: datetime
            column: vat_validated_at
            nullable: true
```

### Update your database schema

```bash
bin/console doctrine:migrations:diff
bin/console doctrine:migrations:migrate
```

## Usage

### Install EU countries and VAT rates

```bash
# EU VAT on digital services (MOSS scheme)
bin/console vat:install:eu

# EU with French VAT (cross-border)
bin/console vat:install:eu FR

# EU with French VAT and passed threshold in Spain and Portugal (cross-border)
bin/console vat:install:eu FR -t ES,PT

# EU with French VAT included in price
bin/console vat:install:eu FR -i

# EU with German standard and reduced VAT categories
bin/console vat:install:eu DE -c standard,reduced
```

### Validate customers VAT number

##### 1. Create new order with VAT number at shipping address
![Screenshot checkout address with vat number](docs/images/checkout_address.png)

##### 2. Show VAT number and validation status at admin orders
![Screenshot order shipping address with vat number](docs/images/admin_order_address.png)


## Testing

Setup
```bash
$ composer install
$ cd tests/Application
$ yarn install
$ yarn run gulp
$ bin/console assets:install public -e test
$ bin/console doctrine:schema:create -e test
$ bin/console server:run 127.0.0.1:8080 -d public -e test
```

Run Tests
```bash
$ vendor/bin/behat
$ vendor/bin/phpspec run
$ vendor/bin/phpstan analyse -c phpstan.neon -l max src/
```

[ico-version]: https://img.shields.io/packagist/v/gweb/sylius-vat-plugin.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/gewebe/SyliusVATPlugin/master.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/gewebe/SyliusVATPlugin.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/gweb/sylius-vat-plugin
[link-travis]: https://travis-ci.org/gewebe/SyliusVATPlugin
[link-code-quality]: https://scrutinizer-ci.com/g/gewebe/SyliusVATPlugin
