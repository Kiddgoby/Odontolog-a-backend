<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260422140310 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add missing columns: age to patient, cara to odontogram_detail, and odontogram JSON to patient';
    }

    public function up(Schema $schema): void
    {
        // Add age column to patient table
        $this->addSql('ALTER TABLE patient ADD age INT DEFAULT NULL');
        
        // Add cara column to odontogram_detail table
        $this->addSql('ALTER TABLE odontogram_detail ADD cara VARCHAR(50) DEFAULT NULL');
        
        // Add odontogram JSON column to patient table
        $this->addSql('ALTER TABLE patient ADD odontogram JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE patient DROP COLUMN age');
        $this->addSql('ALTER TABLE odontogram_detail DROP COLUMN cara');
        $this->addSql('ALTER TABLE patient DROP COLUMN odontogram');
    }
}
