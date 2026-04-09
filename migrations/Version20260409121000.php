<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409121000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add age field to patient entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE patient ADD age INT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE patient DROP age');
    }
}
