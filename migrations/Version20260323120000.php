<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260323120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add password field to dentist table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needss
        $this->addSql('ALTER TABLE dentist ADD password VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dentist DROP COLUMN password');
    }
}
