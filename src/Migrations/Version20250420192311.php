<?php

declare(strict_types=1);

namespace Gewebe\SyliusVATPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250420192311 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add vat number field';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_address ADD vat_number VARCHAR(255) DEFAULT NULL, ADD vat_valid TINYINT(1) NOT NULL, ADD vat_validated_at DATETIME DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_shop_billing_data ADD vat_number VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_address DROP vat_number, DROP vat_valid, DROP vat_validated_at');
        $this->addSql('ALTER TABLE sylius_shop_billing_data DROP vat_number');
    }
}
