<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221104092453 extends AbstractMigration {
    public function getDescription(): string {
        return 'Initial setup';
    }

    public function up(Schema $schema): void {
        $this->addSql('CREATE TABLE placement (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, calendar_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, processor VARCHAR(255) NOT NULL, calendar_category VARCHAR(255) NOT NULL, prefix VARCHAR(255) NOT NULL, name_filter VARCHAR(255) NOT NULL, sheet_name VARCHAR(255) NOT NULL, shifts CLOB DEFAULT NULL, CONSTRAINT FK_48DB750EA40A2C8 FOREIGN KEY (calendar_id) REFERENCES web_dav_calendar (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_48DB750EA40A2C8 ON placement (calendar_id)');
        $this->addSql('CREATE TABLE sync_job (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, placement_id INTEGER DEFAULT NULL, status INTEGER NOT NULL, log CLOB NOT NULL, filename VARCHAR(255) NOT NULL, CONSTRAINT FK_4596994B2F966E9D FOREIGN KEY (placement_id) REFERENCES placement (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4596994B2F966E9D ON sync_job (placement_id)');
        $this->addSql('CREATE TABLE web_dav_calendar (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('DROP TABLE calendar');
    }

    public function down(Schema $schema): void {
        $this->addSql('CREATE TABLE calendar (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE "BINARY", color VARCHAR(255) NOT NULL COLLATE "BINARY", discr VARCHAR(255) NOT NULL COLLATE "BINARY", ical_data VARCHAR(255) DEFAULT NULL COLLATE "BINARY", url VARCHAR(255) DEFAULT NULL COLLATE "BINARY", username VARCHAR(255) DEFAULT NULL COLLATE "BINARY", password VARCHAR(255) DEFAULT NULL COLLATE "BINARY")');
        $this->addSql('DROP TABLE placement');
        $this->addSql('DROP TABLE sync_job');
        $this->addSql('DROP TABLE web_dav_calendar');
    }
}
