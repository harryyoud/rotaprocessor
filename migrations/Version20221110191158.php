<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221110191158 extends AbstractMigration {
    public function getDescription(): string {
        return 'Add owners to entities';
    }

    public function up(Schema $schema): void {
        $this->addSql('DROP TABLE calendar');
        $this->addSql('CREATE TEMPORARY TABLE __temp__placement AS SELECT id, calendar_id, name, processor, calendar_category, prefix, name_filter, sheet_name, shifts FROM placement');
        $this->addSql('DROP TABLE placement');
        $this->addSql('CREATE TABLE placement (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, calendar_id INTEGER DEFAULT NULL, owner_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, processor VARCHAR(255) NOT NULL, calendar_category VARCHAR(255) NOT NULL, prefix VARCHAR(255) NOT NULL, name_filter VARCHAR(255) NOT NULL, sheet_name VARCHAR(255) NOT NULL, shifts CLOB DEFAULT NULL, CONSTRAINT FK_48DB750EA40A2C8 FOREIGN KEY (calendar_id) REFERENCES web_dav_calendar (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_48DB750E7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO placement (id, calendar_id, name, processor, calendar_category, prefix, name_filter, sheet_name, shifts) SELECT id, calendar_id, name, processor, calendar_category, prefix, name_filter, sheet_name, shifts FROM __temp__placement');
        $this->addSql('DROP TABLE __temp__placement');
        $this->addSql('CREATE INDEX IDX_48DB750EA40A2C8 ON placement (calendar_id)');
        $this->addSql('CREATE INDEX IDX_48DB750E7E3C61F9 ON placement (owner_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sync_job AS SELECT id, placement_id, status, log, filename FROM sync_job');
        $this->addSql('DROP TABLE sync_job');
        $this->addSql('CREATE TABLE sync_job (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, placement_id INTEGER DEFAULT NULL, owner_id INTEGER DEFAULT NULL, status INTEGER NOT NULL, log CLOB NOT NULL, filename VARCHAR(255) NOT NULL, CONSTRAINT FK_4596994B2F966E9D FOREIGN KEY (placement_id) REFERENCES placement (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4596994B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sync_job (id, placement_id, status, log, filename) SELECT id, placement_id, status, log, filename FROM __temp__sync_job');
        $this->addSql('DROP TABLE __temp__sync_job');
        $this->addSql('CREATE INDEX IDX_4596994B2F966E9D ON sync_job (placement_id)');
        $this->addSql('CREATE INDEX IDX_4596994B7E3C61F9 ON sync_job (owner_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__web_dav_calendar AS SELECT id, name, color, url, username, password FROM web_dav_calendar');
        $this->addSql('DROP TABLE web_dav_calendar');
        $this->addSql('CREATE TABLE web_dav_calendar (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, owner_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, CONSTRAINT FK_1BED49497E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO web_dav_calendar (id, name, color, url, username, password) SELECT id, name, color, url, username, password FROM __temp__web_dav_calendar');
        $this->addSql('DROP TABLE __temp__web_dav_calendar');
        $this->addSql('CREATE INDEX IDX_1BED49497E3C61F9 ON web_dav_calendar (owner_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE calendar (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL COLLATE "BINARY", color VARCHAR(255) NOT NULL COLLATE "BINARY", discr VARCHAR(255) NOT NULL COLLATE "BINARY", ical_data VARCHAR(255) DEFAULT NULL COLLATE "BINARY", url VARCHAR(255) DEFAULT NULL COLLATE "BINARY", username VARCHAR(255) DEFAULT NULL COLLATE "BINARY", password VARCHAR(255) DEFAULT NULL COLLATE "BINARY")');
        $this->addSql('CREATE TEMPORARY TABLE __temp__placement AS SELECT id, calendar_id, name, processor, calendar_category, prefix, name_filter, sheet_name, shifts FROM placement');
        $this->addSql('DROP TABLE placement');
        $this->addSql('CREATE TABLE placement (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, calendar_id INTEGER DEFAULT NULL, name VARCHAR(255) NOT NULL, processor VARCHAR(255) NOT NULL, calendar_category VARCHAR(255) NOT NULL, prefix VARCHAR(255) NOT NULL, name_filter VARCHAR(255) NOT NULL, sheet_name VARCHAR(255) NOT NULL, shifts CLOB DEFAULT NULL, CONSTRAINT FK_48DB750EA40A2C8 FOREIGN KEY (calendar_id) REFERENCES web_dav_calendar (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO placement (id, calendar_id, name, processor, calendar_category, prefix, name_filter, sheet_name, shifts) SELECT id, calendar_id, name, processor, calendar_category, prefix, name_filter, sheet_name, shifts FROM __temp__placement');
        $this->addSql('DROP TABLE __temp__placement');
        $this->addSql('CREATE INDEX IDX_48DB750EA40A2C8 ON placement (calendar_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__sync_job AS SELECT id, placement_id, status, log, filename FROM sync_job');
        $this->addSql('DROP TABLE sync_job');
        $this->addSql('CREATE TABLE sync_job (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, placement_id INTEGER DEFAULT NULL, status INTEGER NOT NULL, log CLOB NOT NULL, filename VARCHAR(255) NOT NULL, CONSTRAINT FK_4596994B2F966E9D FOREIGN KEY (placement_id) REFERENCES placement (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sync_job (id, placement_id, status, log, filename) SELECT id, placement_id, status, log, filename FROM __temp__sync_job');
        $this->addSql('DROP TABLE __temp__sync_job');
        $this->addSql('CREATE INDEX IDX_4596994B2F966E9D ON sync_job (placement_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__web_dav_calendar AS SELECT id, name, color, url, username, password FROM web_dav_calendar');
        $this->addSql('DROP TABLE web_dav_calendar');
        $this->addSql('CREATE TABLE web_dav_calendar (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, username VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO web_dav_calendar (id, name, color, url, username, password) SELECT id, name, color, url, username, password FROM __temp__web_dav_calendar');
        $this->addSql('DROP TABLE __temp__web_dav_calendar');
    }
}
