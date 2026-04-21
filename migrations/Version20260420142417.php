<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260420142417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Separate cara and notes values in odontogram_detail table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Update records where notes contains "Sec s2:" pattern
        $this->addSql('UPDATE odontogram_detail 
            SET cara = TRIM(SUBSTRING_INDEX(notes, ":", 1)),
                notes = TRIM(SUBSTRING_INDEX(notes, ":", -1))
            WHERE notes LIKE "%:%"');
            
        // Update records where notes contains other patterns like "Vestibular:", "Palatino:", etc.
        $this->addSql('UPDATE odontogram_detail 
            SET cara = TRIM(SUBSTRING_INDEX(notes, ":", 1)),
                notes = TRIM(SUBSTRING_INDEX(notes, ":", -1))
            WHERE notes LIKE "%:%" AND cara IS NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // Reconstruct original notes format
        $this->addSql('UPDATE odontogram_detail 
            SET notes = CONCAT(IFNULL(cara, ""), ": ", IFNULL(notes, ""))
            WHERE cara IS NOT NULL');
    }
}
