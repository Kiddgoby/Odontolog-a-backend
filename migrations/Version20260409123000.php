<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260409123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Insert tooth records with fixed IDs for adult and child teeth';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('CREATE TABLE tooth_new LIKE tooth');
        $this->addSql("INSERT INTO tooth_new (id, description) VALUES
            (18, '18'), (17, '17'), (16, '16'), (15, '15'), (14, '14'), (13, '13'), (12, '12'), (11, '11'),
            (21, '21'), (22, '22'), (23, '23'), (24, '24'), (25, '25'), (26, '26'), (27, '27'), (28, '28'),
            (48, '48'), (47, '47'), (46, '46'), (45, '45'), (44, '44'), (43, '43'), (42, '42'), (41, '41'),
            (31, '31'), (32, '32'), (33, '33'), (34, '34'), (35, '35'), (36, '36'), (37, '37'), (38, '38'),
            (55, '55'), (54, '54'), (53, '53'), (52, '52'), (51, '51'),
            (61, '61'), (62, '62'), (63, '63'), (64, '64'), (65, '65'),
            (85, '85'), (84, '84'), (83, '83'), (82, '82'), (81, '81'),
            (71, '71'), (72, '72'), (73, '73'), (74, '74'), (75, '75')
        ");
        $this->addSql("UPDATE odontogram_detail od
            JOIN tooth t ON od.tooth_id = t.id
            SET od.tooth_id = CAST(REPLACE(t.description, 'Tooth ', '') AS UNSIGNED)");
        $this->addSql('ALTER TABLE odontogram_detail DROP FOREIGN KEY FK_217F5C27A2A44441');
        $this->addSql('DROP TABLE tooth');
        $this->addSql('RENAME TABLE tooth_new TO tooth');
        $this->addSql('ALTER TABLE odontogram_detail ADD CONSTRAINT FK_217F5C27A2A44441 FOREIGN KEY (tooth_id) REFERENCES tooth (id)');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('SET FOREIGN_KEY_CHECKS = 0');
        $this->addSql('ALTER TABLE odontogram_detail DROP FOREIGN KEY FK_217F5C27A2A44441');
        $this->addSql('CREATE TABLE tooth_old LIKE tooth');
        $this->addSql("INSERT INTO tooth_old (id, description) VALUES
            (1, 'Tooth 11'), (2, 'Tooth 12'), (3, 'Tooth 13'), (4, 'Tooth 14'), (5, 'Tooth 15'), (6, 'Tooth 16'), (7, 'Tooth 17'), (8, 'Tooth 18'),
            (9, 'Tooth 21'), (10, 'Tooth 22'), (11, 'Tooth 23'), (12, 'Tooth 24'), (13, 'Tooth 25'), (14, 'Tooth 26'), (15, 'Tooth 27'), (16, 'Tooth 28'),
            (17, 'Tooth 31'), (18, 'Tooth 32'), (19, 'Tooth 33'), (20, 'Tooth 34'), (21, 'Tooth 35'), (22, 'Tooth 36'), (23, 'Tooth 37'), (24, 'Tooth 38'),
            (25, 'Tooth 41'), (26, 'Tooth 42'), (27, 'Tooth 43'), (28, 'Tooth 44'), (29, 'Tooth 45'), (30, 'Tooth 46'), (31, 'Tooth 47'), (32, 'Tooth 48')
        ");
        $this->addSql("UPDATE odontogram_detail od
            JOIN tooth t ON od.tooth_id = t.id
            SET od.tooth_id = CASE CAST(t.description AS UNSIGNED)
                WHEN 11 THEN 1
                WHEN 12 THEN 2
                WHEN 13 THEN 3
                WHEN 14 THEN 4
                WHEN 15 THEN 5
                WHEN 16 THEN 6
                WHEN 17 THEN 7
                WHEN 18 THEN 8
                WHEN 21 THEN 9
                WHEN 22 THEN 10
                WHEN 23 THEN 11
                WHEN 24 THEN 12
                WHEN 25 THEN 13
                WHEN 26 THEN 14
                WHEN 27 THEN 15
                WHEN 28 THEN 16
                WHEN 31 THEN 17
                WHEN 32 THEN 18
                WHEN 33 THEN 19
                WHEN 34 THEN 20
                WHEN 35 THEN 21
                WHEN 36 THEN 22
                WHEN 37 THEN 23
                WHEN 38 THEN 24
                WHEN 41 THEN 25
                WHEN 42 THEN 26
                WHEN 43 THEN 27
                WHEN 44 THEN 28
                WHEN 45 THEN 29
                WHEN 46 THEN 30
                WHEN 47 THEN 31
                WHEN 48 THEN 32
                ELSE od.tooth_id
            END");
        $this->addSql('DROP TABLE tooth');
        $this->addSql('RENAME TABLE tooth_old TO tooth');
        $this->addSql('ALTER TABLE odontogram_detail ADD CONSTRAINT FK_217F5C27A2A44441 FOREIGN KEY (tooth_id) REFERENCES tooth (id)');
        $this->addSql('SET FOREIGN_KEY_CHECKS = 1');
    }
}
