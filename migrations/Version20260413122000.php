<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260413122000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add category, duration and price fields to treatment entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE treatment ADD category VARCHAR(100) NOT NULL DEFAULT ''");
        $this->addSql("ALTER TABLE treatment ADD duration INT NOT NULL DEFAULT 0");
        $this->addSql("ALTER TABLE treatment ADD price NUMERIC(10,2) NOT NULL DEFAULT '0.00'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE treatment DROP COLUMN category');
        $this->addSql('ALTER TABLE treatment DROP COLUMN duration');
        $this->addSql('ALTER TABLE treatment DROP COLUMN price');
    }
}
