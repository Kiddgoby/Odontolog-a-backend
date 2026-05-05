<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration para actualizar nombres de tratamientos y patologías
 * Cambia 'Gingivitis' por 'Fisura' en patología
 * Cambia 'Limpieza' por 'Rayos X' en tratamiento
 */
final class UpdateTreatmentAndPathologyNames extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Actualiza nombres de tratamientos y patologías: Gingivitis -> Fisura, Limpieza -> Rayos X';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            -- Actualizar patología: Gingivitis -> Fisura
            UPDATE pathology 
            SET description = 'Fisura' 
            WHERE description = 'Gingivitis';
            
            -- Actualizar tratamiento: Limpieza -> Rayos X
            UPDATE treatment 
            SET treatment_name = 'Rayos X' 
            WHERE treatment_name = 'Limpieza';
            
            -- Eliminar columnas no deseadas de treatment
            ALTER TABLE treatment 
            DROP COLUMN category,
            DROP COLUMN duration,
            DROP COLUMN price;
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("
            -- Revertir patología: Fisura -> Gingivitis
            UPDATE pathology 
            SET description = 'Gingivitis' 
            WHERE description = 'Fisura';
            
            -- Revertir tratamiento: Rayos X -> Limpieza
            UPDATE treatment 
            SET treatment_name = 'Limpieza' 
            WHERE treatment_name = 'Rayos X';
            
            -- Restaurar columnas eliminadas de treatment
            ALTER TABLE treatment 
            ADD COLUMN category VARCHAR(100) NULL,
            ADD COLUMN duration INT NULL,
            ADD COLUMN price DECIMAL(10,2) NULL;
        ");
    }
}
