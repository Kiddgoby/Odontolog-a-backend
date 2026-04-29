<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add treatment_id column to odontogram_detail table
 */
final class Version20260427120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add treatment_id column to odontogram_detail table';
    }

    public function up(Schema $schema): void
    {
        // Add treatment_id column
        $this->addSql('ALTER TABLE odontogram_detail ADD treatment_id INT DEFAULT NULL');
        
        // Add foreign key constraint
        $this->addSql('ALTER TABLE odontogram_detail ADD CONSTRAINT FK_217F5C27471C0366 FOREIGN KEY (treatment_id) REFERENCES treatment (id)');
        
        // Create index
        $this->addSql('CREATE INDEX IDX_217F5C27471C0366 ON odontogram_detail (treatment_id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key
        $this->addSql('ALTER TABLE odontogram_detail DROP FOREIGN KEY FK_217F5C27471C0366');
        
        // Drop column
        $this->addSql('ALTER TABLE odontogram_detail DROP treatment_id');
    }
}
