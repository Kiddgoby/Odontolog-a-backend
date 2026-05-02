<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260502080000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add status_id column to odontogram_detail table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SET @column_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = \'odontogram_detail\' AND column_name = \'status_id\')');
        
        $this->addSql('SET @add_column = IF @column_exists = 0 THEN
            ALTER TABLE odontogram_detail ADD status_id INT DEFAULT NULL;
        END IF');
        
        $this->addSql('SET @add_fk = IF @column_exists = 0 THEN
            ALTER TABLE odontogram_detail ADD CONSTRAINT FK_217F5C276BF700BD FOREIGN KEY (status_id) REFERENCES status(id);
        END IF');
        
        $this->addSql('SET @add_index = IF @column_exists = 0 THEN
            CREATE INDEX IDX_217F5C276BF700BD ON odontogram_detail (status_id);
        END IF');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE odontogram_detail DROP FOREIGN KEY FK_217F5C276BF700BD');
        
        $this->addSql('DROP INDEX IDX_217F5C276BF700BD ON odontogram_detail');
        
        $this->addSql('ALTER TABLE odontogram_detail DROP COLUMN status_id');
    }
}
