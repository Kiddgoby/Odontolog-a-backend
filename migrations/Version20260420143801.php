<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260420143801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Fix cara and notes separation - cara stores face info, notes stores text only';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        // Fix records where notes still contains "cara: text" format
        $this->addSql('UPDATE odontogram_detail 
            SET cara = TRIM(SUBSTRING_INDEX(notes, ":", 1)),
                notes = TRIM(SUBSTRING_INDEX(notes, ":", -1))
            WHERE notes LIKE "%:%" AND cara IS NULL');
            
        // Fix records where cara is duplicated in notes
        $this->addSql('UPDATE odontogram_detail 
            SET notes = TRIM(SUBSTRING_INDEX(notes, ":", -1))
            WHERE notes LIKE CONCAT(cara, ":%") AND cara IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // Restore original format (optional - for testing purposes)
        $this->addSql('UPDATE odontogram_detail 
            SET notes = CONCAT(IFNULL(cara, ""), ": ", IFNULL(notes, ""))
            WHERE cara IS NOT NULL');
    }
}
