<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20221110234150 extends AbstractMigration {
    public function getDescription(): string {
        return 'Add createdAt to syncjob';
    }

    public function up(Schema $schema): void {
        $this->addSql('ALTER TABLE sync_job ADD COLUMN created_at DATETIME NOT NULL');
    }

    public function down(Schema $schema): void {
        $this->addSql('CREATE TEMPORARY TABLE __temp__sync_job AS SELECT id, placement_id, owner_id, status, log, filename FROM sync_job');
        $this->addSql('DROP TABLE sync_job');
        $this->addSql('CREATE TABLE sync_job (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, placement_id INTEGER DEFAULT NULL, owner_id INTEGER DEFAULT NULL, status INTEGER NOT NULL, log CLOB NOT NULL, filename VARCHAR(255) NOT NULL, CONSTRAINT FK_4596994B2F966E9D FOREIGN KEY (placement_id) REFERENCES placement (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_4596994B7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO sync_job (id, placement_id, owner_id, status, log, filename) SELECT id, placement_id, owner_id, status, log, filename FROM __temp__sync_job');
        $this->addSql('DROP TABLE __temp__sync_job');
        $this->addSql('CREATE INDEX IDX_4596994B2F966E9D ON sync_job (placement_id)');
        $this->addSql('CREATE INDEX IDX_4596994B7E3C61F9 ON sync_job (owner_id)');
    }
}
