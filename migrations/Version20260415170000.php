<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!!
 */
final class Version20260415170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove odontogram column from patient table';
    }

    public function up(Schema $schema): void
    {
        // Eliminar la columna odontogram de la tabla patient si existe
        $table = $schema->getTable('patient');
        if ($table->hasColumn('odontogram')) {
            $this->addSql('ALTER TABLE patient DROP COLUMN odontogram');
        }
    }

    public function down(Schema $schema): void
    {
        // Volver a agregar la columna odontogram si se necesita rollback
        $this->addSql('ALTER TABLE patient ADD odontogram LONGTEXT DEFAULT NULL');
    }
}
