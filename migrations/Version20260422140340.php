<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migración para poblar pathology_id y treatment_id basada en las notas existentes.
 */
final class Version20260422140340 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populate pathology_id and treatment_id in odontogram_detail table from pathology and treatment reference tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("UPDATE odontogram_detail SET pathology_id = 1 WHERE LOWER(notes) LIKE '%caries%'");
        $this->addSql("UPDATE odontogram_detail SET pathology_id = 2 WHERE LOWER(notes) LIKE '%ausencia%'");
        $this->addSql("UPDATE odontogram_detail SET pathology_id = 3 WHERE LOWER(notes) LIKE '%sellado%'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('UPDATE odontogram_detail SET pathology_id = NULL');
    }
}