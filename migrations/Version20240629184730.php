<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240629184730 extends AbstractMigration {
    public function getDescription(): string {
        return 'Switch from nuh_medicine to nuh_qmc_medicine';
    }

    public function up(Schema $schema): void {
        $this->addSql('UPDATE placement SET processor = "nuh_qmc_medicine" WHERE processor = "nuh_medicine"');
    }

    public function down(Schema $schema): void {
        $this->addSql('UPDATE placement SET processor = "nuh_medicine" WHERE processor = "nuh_qmc_medicine"');
    }
}
