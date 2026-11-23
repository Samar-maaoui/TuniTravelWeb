<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250325010158 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evenements (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, lieu VARCHAR(255) NOT NULL, date_evenement DATE NOT NULL, capacite INT NOT NULL, type VARCHAR(255) NOT NULL, organisateur VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE excursion (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, lieu VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, date_depart DATETIME NOT NULL, date_retour DATETIME NOT NULL, disponibilite TINYINT(1) NOT NULL, description LONGTEXT NOT NULL, nombre_max_participants INT NOT NULL, id_guide INT NOT NULL, image VARCHAR(255) NOT NULL, ville_depart VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE excursionstat (id INT AUTO_INCREMENT NOT NULL, excursion_id INT NOT NULL, reservations_count INT NOT NULL, total_reserved_places INT NOT NULL, date_created DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE feedback (id_feedback INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, commentaire LONGTEXT NOT NULL, note INT DEFAULT NULL, date_feedback DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id_feedback)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guide (id_guide INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, disponibilite TINYINT(1) NOT NULL, langues VARCHAR(255) NOT NULL, numero_tel VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id_guide)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hotel (id_hotel INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prix_nuit DOUBLE PRECISION NOT NULL, est_disponible TINYINT(1) NOT NULL, adresse VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, tel VARCHAR(255) NOT NULL, email_contact VARCHAR(255) NOT NULL, image_hotel VARCHAR(255) NOT NULL, PRIMARY KEY(id_hotel)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE packvoyage (id_pack INT AUTO_INCREMENT NOT NULL, admin_id INT NOT NULL, vol_id INT NOT NULL, hotel_id INT NOT NULL, excursion_id INT NOT NULL, prix_total DOUBLE PRECISION NOT NULL, details VARCHAR(255) NOT NULL, nombre_places_restants INT NOT NULL, nom_pack VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, PRIMARY KEY(id_pack)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post (id_post INT AUTO_INCREMENT NOT NULL, titre VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_publication DATETIME NOT NULL, date_expiration DATETIME NOT NULL, pack_id INT NOT NULL, statut VARCHAR(255) NOT NULL, image_url VARCHAR(255) NOT NULL, PRIMARY KEY(id_post)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservation (id_reservation INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, date_reservation DATETIME NOT NULL, statut VARCHAR(255) NOT NULL, montant_total DOUBLE PRECISION NOT NULL, PRIMARY KEY(id_reservation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservationexcursion (id_reservation INT AUTO_INCREMENT NOT NULL, excursion_id INT NOT NULL, guide_id INT NOT NULL, nbr_place_reservee INT NOT NULL, PRIMARY KEY(id_reservation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservationhotel (id_reservation INT AUTO_INCREMENT NOT NULL, hotel_id INT NOT NULL, check_in_date DATETIME NOT NULL, check_out_date DATETIME NOT NULL, type_chambre VARCHAR(255) NOT NULL, PRIMARY KEY(id_reservation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservationpackvoyage (id_reservation INT AUTO_INCREMENT NOT NULL, utilisateur_id INT NOT NULL, date_reservation DATETIME NOT NULL, statut VARCHAR(255) NOT NULL, statut_paiement VARCHAR(255) NOT NULL, pack_id INT NOT NULL, post_id INT NOT NULL, places_demandees INT NOT NULL, montant_total DOUBLE PRECISION NOT NULL, PRIMARY KEY(id_reservation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservationvol (id_reservation INT AUTO_INCREMENT NOT NULL, vol_id INT NOT NULL, classe_service VARCHAR(255) NOT NULL, PRIMARY KEY(id_reservation)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE utilisateur (id_utilisateur DOUBLE PRECISION NOT NULL, nom VARCHAR(255) NOT NULL, prénom VARCHAR(255) NOT NULL, cin DOUBLE PRECISION NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, date_naissance DATETIME NOT NULL, telephone DOUBLE PRECISION NOT NULL, type_utilisateur VARCHAR(255) NOT NULL, is_verified DOUBLE PRECISION NOT NULL, date_creation DATETIME NOT NULL, PRIMARY KEY(id_utilisateur)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vol (id_vol DOUBLE PRECISION NOT NULL, numero_vol VARCHAR(255) NOT NULL, compagnie VARCHAR(255) NOT NULL, prix DOUBLE PRECISION NOT NULL, disponible DOUBLE PRECISION NOT NULL, aero_depart VARCHAR(255) NOT NULL, aero_arrivee VARCHAR(255) NOT NULL, date_depart DATETIME NOT NULL, date_arrivee DATETIME NOT NULL, PRIMARY KEY(id_vol)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE person');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE person (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prenon VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, age INT NOT NULL) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('DROP TABLE evenements');
        $this->addSql('DROP TABLE excursion');
        $this->addSql('DROP TABLE excursionstat');
        $this->addSql('DROP TABLE feedback');
        $this->addSql('DROP TABLE guide');
        $this->addSql('DROP TABLE hotel');
        $this->addSql('DROP TABLE packvoyage');
        $this->addSql('DROP TABLE post');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE reservationexcursion');
        $this->addSql('DROP TABLE reservationhotel');
        $this->addSql('DROP TABLE reservationpackvoyage');
        $this->addSql('DROP TABLE reservationvol');
        $this->addSql('DROP TABLE utilisateur');
        $this->addSql('DROP TABLE vol');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
