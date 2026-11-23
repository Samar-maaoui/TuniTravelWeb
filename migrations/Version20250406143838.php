<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250406143838 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE evenements MODIFY id INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `primary` ON evenements
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenements CHANGE id id_evenement INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenements ADD PRIMARY KEY (id_evenement)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE excursion CHANGE date_depart date_depart DATE NOT NULL, CHANGE date_retour date_retour DATE NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE excursion ADD CONSTRAINT FK_9B08E72F241B8605 FOREIGN KEY (id_guide) REFERENCES guide (id_guide)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_9B08E72F241B8605 ON excursion (id_guide)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE packvoyage CHANGE vol_id vol_id DOUBLE PRECISION DEFAULT NULL, CHANGE hotel_id hotel_id INT DEFAULT NULL, CHANGE excursion_id excursion_id INT DEFAULT NULL, CHANGE nom_pack nomPack VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE packvoyage ADD CONSTRAINT FK_751EFB83243BB18 FOREIGN KEY (hotel_id) REFERENCES hotel (id_hotel)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE packvoyage ADD CONSTRAINT FK_751EFB89F2BFB7A FOREIGN KEY (vol_id) REFERENCES vol (id_vol)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE packvoyage ADD CONSTRAINT FK_751EFB84AB4296F FOREIGN KEY (excursion_id) REFERENCES excursion (id_excursion)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_751EFB83243BB18 ON packvoyage (hotel_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_751EFB89F2BFB7A ON packvoyage (vol_id)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_751EFB84AB4296F ON packvoyage (excursion_id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D1919B217 FOREIGN KEY (pack_id) REFERENCES packvoyage (id_pack)
        SQL);
        $this->addSql(<<<'SQL'
            CREATE INDEX IDX_5A8A6C8D1919B217 ON post (pack_id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE evenements MODIFY id_evenement INT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX `PRIMARY` ON evenements
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenements CHANGE id_evenement id INT AUTO_INCREMENT NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE evenements ADD PRIMARY KEY (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE excursion DROP FOREIGN KEY FK_9B08E72F241B8605
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_9B08E72F241B8605 ON excursion
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE excursion CHANGE date_depart date_depart DATETIME NOT NULL, CHANGE date_retour date_retour DATETIME NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE packvoyage DROP FOREIGN KEY FK_751EFB83243BB18
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE packvoyage DROP FOREIGN KEY FK_751EFB89F2BFB7A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE packvoyage DROP FOREIGN KEY FK_751EFB84AB4296F
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_751EFB83243BB18 ON packvoyage
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_751EFB89F2BFB7A ON packvoyage
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_751EFB84AB4296F ON packvoyage
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE packvoyage CHANGE hotel_id hotel_id INT NOT NULL, CHANGE vol_id vol_id INT NOT NULL, CHANGE excursion_id excursion_id INT NOT NULL, CHANGE nomPack nom_pack VARCHAR(255) NOT NULL
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D1919B217
        SQL);
        $this->addSql(<<<'SQL'
            DROP INDEX IDX_5A8A6C8D1919B217 ON post
        SQL);
    }
}
