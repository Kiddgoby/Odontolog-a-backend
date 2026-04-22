<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260420152339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add treatment_id column to odontogram_detail table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE odontogram_detail ADD treatment_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE odontogram_detail ADD CONSTRAINT FK_217F5C274710C2F9 FOREIGN KEY (treatment_id) REFERENCES treatment (id)');
        $this->addSql('CREATE INDEX IDX_217F5C274710C2F9 ON odontogram_detail (treatment_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE odontogram_detail DROP FOREIGN KEY FK_217F5C274710C2F9');
        $this->addSql('DROP INDEX IDX_217F5C274710C2F9 ON odontogram_detail');
        $this->addSql('ALTER TABLE odontogram_detail DROP treatment_id');
    }
}
