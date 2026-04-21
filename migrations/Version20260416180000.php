<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration to populate pathology and treatment tables with specific data
 */
final class Version20260416180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Populate pathology and treatment tables with Caries, Ausencia, Sellado, Pendiente, Realizado';
    }

    public function up(Schema $schema): void
    {
        // Deshabilitar restricciones de clave externa
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        
        // Limpiar tablas existentes
        $this->addSql('DELETE FROM odontogram_detail');
        $this->addSql('DELETE FROM pathology');
        $this->addSql('DELETE FROM treatment');

        // Insertar datos en tabla pathology (mantener los existentes)
        $this->addSql('INSERT INTO pathology (id, description) VALUES (1, "Caries")');
        $this->addSql('INSERT INTO pathology (id, description) VALUES (2, "Ausencia")');
        $this->addSql('INSERT INTO pathology (id, description) VALUES (3, "Sellado")');

        // Insertar datos en tabla treatment (solo Pendiente y Realizado)
        $this->addSql('INSERT INTO treatment (id, treatment_name, description) VALUES (1, "Pendiente", "Tratamiento pendiente de realizar")');
        $this->addSql('INSERT INTO treatment (id, treatment_name, description) VALUES (2, "Realizado", "Tratamiento ya realizado")');
        
        // Rehabilitar restricciones de clave externa
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        // Deshabilitar restricciones de clave externa
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        
        // Limpiar tablas
        $this->addSql('DELETE FROM odontogram_detail');
        $this->addSql('DELETE FROM pathology');
        $this->addSql('DELETE FROM treatment');
        
        // Rehabilitar restricciones de clave externa
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }
}
