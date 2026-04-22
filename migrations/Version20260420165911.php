<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260420165911 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populate pathology_id and treatment_id in odontogram_detail table from pathology and treatment reference tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        // Update odontogram_detail records to set pathology_id based on notes content
        // If notes contain "Caries", set pathology_id to 1 (Caries)
        $this->addSql('UPDATE odontogram_detail SET pathology_id = 1 WHERE notes LIKE "%Caries%" OR notes LIKE "%caries%"');
        
        // If notes contain "Ausencia", set pathology_id to 2 (Ausencia)
        $this->addSql('UPDATE odontogram_detail SET pathology_id = 2 WHERE notes LIKE "%Ausencia%" OR notes LIKE "%ausencia%"');
        
        // If notes contain "Sellado", set pathology_id to 3 (Sellado)
        $this->addSql('UPDATE odontogram_detail SET pathology_id = 3 WHERE notes LIKE "%Sellado%" OR notes LIKE "%sellado%"');
        
        // Set default treatment_id to 1 (Pendiente) for all records that don't have one
        $this->addSql('UPDATE odontogram_detail SET treatment_id = 1 WHERE treatment_id IS NULL');
        
        // If notes contain "Realizado" or "realizado", set treatment_id to 2 (Realizado)
        $this->addSql('UPDATE odontogram_detail SET treatment_id = 2 WHERE notes LIKE "%Realizado%" OR notes LIKE "%realizado%"');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        
        // Reset pathology_id and treatment_id to NULL
        $this->addSql('UPDATE odontogram_detail SET pathology_id = NULL, treatment_id = NULL');
    }
}
