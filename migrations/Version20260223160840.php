<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260223160840 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE appointment (id INT AUTO_INCREMENT NOT NULL, visit_date DATE NOT NULL, consultation_reason LONGTEXT DEFAULT NULL, patient_id INT NOT NULL, dentist_id INT NOT NULL, box_id INT NOT NULL, treatment_id INT NOT NULL, INDEX IDX_FE38F8446B899279 (patient_id), INDEX IDX_FE38F8441CE0A142 (dentist_id), INDEX IDX_FE38F844D8177B3F (box_id), INDEX IDX_FE38F844471C0366 (treatment_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE box (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, capacity INT NOT NULL, status VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE dentist (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, specialty VARCHAR(100) NOT NULL, available_days VARCHAR(100) NOT NULL, phone VARCHAR(20) NOT NULL, email VARCHAR(100) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE document (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(100) NOT NULL, file_url VARCHAR(255) NOT NULL, capture_date DATE NOT NULL, patient_id INT NOT NULL, INDEX IDX_D8698A766B899279 (patient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE odontogram (id INT AUTO_INCREMENT NOT NULL, creation_date DATE NOT NULL, patient_id INT NOT NULL, appointment_id INT NOT NULL, INDEX IDX_251BF9406B899279 (patient_id), INDEX IDX_251BF940E5B533F9 (appointment_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE odontogram_detail (id INT AUTO_INCREMENT NOT NULL, notes LONGTEXT DEFAULT NULL, odontogram_id INT NOT NULL, tooth_id INT NOT NULL, pathology_id INT NOT NULL, INDEX IDX_217F5C2759C0DBCD (odontogram_id), INDEX IDX_217F5C27A2A44441 (tooth_id), INDEX IDX_217F5C27CE86795D (pathology_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE pathology (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, national_id INT NOT NULL, social_security_number VARCHAR(20) NOT NULL, phone VARCHAR(20) NOT NULL, email VARCHAR(100) NOT NULL, address VARCHAR(255) NOT NULL, billing_data VARCHAR(500) NOT NULL, health_status VARCHAR(500) NOT NULL, family_history VARCHAR(500) NOT NULL, lifestyle_habits VARCHAR(500) NOT NULL, medication_allergies VARCHAR(500) NOT NULL, registration_date DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE tooth (id INT AUTO_INCREMENT NOT NULL, description VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE treatment (id INT AUTO_INCREMENT NOT NULL, treatment_name VARCHAR(150) NOT NULL, description LONGTEXT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8446B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F8441CE0A142 FOREIGN KEY (dentist_id) REFERENCES dentist (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844D8177B3F FOREIGN KEY (box_id) REFERENCES box (id)');
        $this->addSql('ALTER TABLE appointment ADD CONSTRAINT FK_FE38F844471C0366 FOREIGN KEY (treatment_id) REFERENCES treatment (id)');
        $this->addSql('ALTER TABLE document ADD CONSTRAINT FK_D8698A766B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE odontogram ADD CONSTRAINT FK_251BF9406B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE odontogram ADD CONSTRAINT FK_251BF940E5B533F9 FOREIGN KEY (appointment_id) REFERENCES appointment (id)');
        $this->addSql('ALTER TABLE odontogram_detail ADD CONSTRAINT FK_217F5C2759C0DBCD FOREIGN KEY (odontogram_id) REFERENCES odontogram (id)');
        $this->addSql('ALTER TABLE odontogram_detail ADD CONSTRAINT FK_217F5C27A2A44441 FOREIGN KEY (tooth_id) REFERENCES tooth (id)');
        $this->addSql('ALTER TABLE odontogram_detail ADD CONSTRAINT FK_217F5C27CE86795D FOREIGN KEY (pathology_id) REFERENCES pathology (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8446B899279');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F8441CE0A142');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844D8177B3F');
        $this->addSql('ALTER TABLE appointment DROP FOREIGN KEY FK_FE38F844471C0366');
        $this->addSql('ALTER TABLE document DROP FOREIGN KEY FK_D8698A766B899279');
        $this->addSql('ALTER TABLE odontogram DROP FOREIGN KEY FK_251BF9406B899279');
        $this->addSql('ALTER TABLE odontogram DROP FOREIGN KEY FK_251BF940E5B533F9');
        $this->addSql('ALTER TABLE odontogram_detail DROP FOREIGN KEY FK_217F5C2759C0DBCD');
        $this->addSql('ALTER TABLE odontogram_detail DROP FOREIGN KEY FK_217F5C27A2A44441');
        $this->addSql('ALTER TABLE odontogram_detail DROP FOREIGN KEY FK_217F5C27CE86795D');
        $this->addSql('DROP TABLE appointment');
        $this->addSql('DROP TABLE box');
        $this->addSql('DROP TABLE dentist');
        $this->addSql('DROP TABLE document');
        $this->addSql('DROP TABLE odontogram');
        $this->addSql('DROP TABLE odontogram_detail');
        $this->addSql('DROP TABLE pathology');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE tooth');
        $this->addSql('DROP TABLE treatment');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
