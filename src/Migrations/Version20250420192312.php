<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

final class Version20250420192312 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'Add VAT number fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_address ADD vat_number VARCHAR(255), ADD vat_valid BOOLEAN NOT NULL, ADD vat_validated_at TIMESTAMP DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shop_billing_data ADD vat_number VARCHAR(255)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_address DROP COLUMN vat_number, DROP COLUMN vat_valid, DROP COLUMN vat_validated_at');
        $this->addSql('ALTER TABLE sylius_shop_billing_data DROP COLUMN vat_number');
    }
}
