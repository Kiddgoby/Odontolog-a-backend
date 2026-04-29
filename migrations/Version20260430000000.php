<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!!
 */
final class Version20260430000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add box_id and pathology_id to dentist table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dentist ADD box_id INT NOT NULL, ADD pathology_id INT NOT NULL');
        $this->addSql('ALTER TABLE dentist ADD CONSTRAINT FK_dentist_box FOREIGN KEY (box_id) REFERENCES box (id)');
        $this->addSql('ALTER TABLE dentist ADD CONSTRAINT FK_dentist_pathology FOREIGN KEY (pathology_id) REFERENCES pathology (id)');
        $this->addSql('CREATE INDEX IDX_dentist_box ON dentist (box_id)');
        $this->addSql('CREATE INDEX IDX_dentist_pathology ON dentist (pathology_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE dentist DROP FOREIGN KEY FK_dentist_box');
        $this->addSql('ALTER TABLE dentist DROP FOREIGN KEY FK_dentist_pathology');
        $this->addSql('DROP INDEX IDX_dentist_box ON dentist');
        $this->addSql('DROP INDEX IDX_dentist_pathology ON dentist');
        $this->addSql('ALTER TABLE dentist DROP box_id, DROP pathology_id');
    }
}