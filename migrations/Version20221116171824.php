<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221116171824 extends AbstractMigration {
    public function getDescription(): string {
        return 'Initial migration';
    }

    public function up(Schema $schema): void {
        $this->addSql('CREATE TABLE placement (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', calendar_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', owner_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, processor VARCHAR(255) NOT NULL, calendar_category VARCHAR(255) NOT NULL, prefix VARCHAR(255) NOT NULL, name_filter VARCHAR(255) NOT NULL, sheet_name VARCHAR(255) NOT NULL, shifts LONGTEXT DEFAULT NULL, INDEX IDX_48DB750EA40A2C8 (calendar_id), INDEX IDX_48DB750E7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sync_job (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', placement_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', owner_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', status INT NOT NULL, log LONGTEXT NOT NULL, filename VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4596994B2F966E9D (placement_id), INDEX IDX_4596994B7E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE web_dav_calendar (id BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', owner_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:uuid)\', name VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, INDEX IDX_1BED49497E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE placement ADD CONSTRAINT FK_48DB750EA40A2C8 FOREIGN KEY (calendar_id) REFERENCES web_dav_calendar (id)');
        $this->addSql('ALTER TABLE placement ADD CONSTRAINT FK_48DB750E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE sync_job ADD CONSTRAINT FK_4596994B2F966E9D FOREIGN KEY (placement_id) REFERENCES placement (id)');
        $this->addSql('ALTER TABLE sync_job ADD CONSTRAINT FK_4596994B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE web_dav_calendar ADD CONSTRAINT FK_1BED49497E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void {
        $this->addSql('ALTER TABLE placement DROP FOREIGN KEY FK_48DB750EA40A2C8');
        $this->addSql('ALTER TABLE placement DROP FOREIGN KEY FK_48DB750E7E3C61F9');
        $this->addSql('ALTER TABLE sync_job DROP FOREIGN KEY FK_4596994B2F966E9D');
        $this->addSql('ALTER TABLE sync_job DROP FOREIGN KEY FK_4596994B7E3C61F9');
        $this->addSql('ALTER TABLE web_dav_calendar DROP FOREIGN KEY FK_1BED49497E3C61F9');
        $this->addSql('DROP TABLE placement');
        $this->addSql('DROP TABLE sync_job');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE web_dav_calendar');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
