<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260420145859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populate pathology table with Caries, Ausencia, Sellado and treatment table with Pendiente, Realizado';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Deshabilitar restricciones de clave externa
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        
        // Limpiar tablas existentes
        $this->addSql('DELETE FROM odontogram_detail');
        $this->addSql('DELETE FROM pathology');
        $this->addSql('DELETE FROM treatment');

        // Insertar datos en tabla pathology
        $this->addSql('INSERT INTO pathology (id, description) VALUES (1, "Caries")');
        $this->addSql('INSERT INTO pathology (id, description) VALUES (2, "Ausencia")');
        $this->addSql('INSERT INTO pathology (id, description) VALUES (3, "Sellado")');

        // Insertar datos en tabla treatment
        $this->addSql('INSERT INTO treatment (id, treatment_name, description) VALUES (1, "Pendiente", "Tratamiento pendiente de realizar")');
        $this->addSql('INSERT INTO treatment (id, treatment_name, description) VALUES (2, "Realizado", "Tratamiento ya realizado")');
        
        // Rehabilitar restricciones de clave externa
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // Limpiar tablas
        $this->addSql('DELETE FROM odontogram_detail');
        $this->addSql('DELETE FROM pathology');
        $this->addSql('DELETE FROM treatment');
    }
}
