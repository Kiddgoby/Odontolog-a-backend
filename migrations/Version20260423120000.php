<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Make pathology_id nullable in odontogram_detail table
 */
final class Version20260423120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make pathology_id nullable in odontogram_detail table';
    }

    public function up(Schema $schema): void
    {
        // Drop foreign key
        $this->addSql('ALTER TABLE odontogram_detail DROP FOREIGN KEY FK_217F5C27CE86795D');
        
        // Modify column to allow NULL
        $this->addSql('ALTER TABLE odontogram_detail MODIFY pathology_id INT DEFAULT NULL');
        
        // Recreate foreign key
        $this->addSql('ALTER TABLE odontogram_detail ADD CONSTRAINT FK_217F5C27CE86795D FOREIGN KEY (pathology_id) REFERENCES pathology (id)');
    }

    public function down(Schema $schema): void
    {
        // Drop foreign key
        $this->addSql('ALTER TABLE odontogram_detail DROP FOREIGN KEY FK_217F5C27CE86795D');
        
        // Restore NOT NULL constraint
        $this->addSql('ALTER TABLE odontogram_detail MODIFY pathology_id INT NOT NULL');
        
        // Recreate foreign key
        $this->addSql('ALTER TABLE odontogram_detail ADD CONSTRAINT FK_217F5C27CE86795D FOREIGN KEY (pathology_id) REFERENCES pathology (id)');
    }
}
