<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261001000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création des tables utilisateur, trajet et reservation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE utilisateur (id INT AUTO_INCREMENT NOT NULL, prenom VARCHAR(100) NOT NULL, nom VARCHAR(100) NOT NULL, nom_utilisateur VARCHAR(60) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, photo VARCHAR(255) DEFAULT NULL, date_inscription DATETIME NOT NULL, reset_token VARCHAR(100) DEFAULT NULL, reset_token_expire_le DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1D1C63B3D37CC8AC (nom_utilisateur), UNIQUE INDEX UNIQ_1D1C63B3E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE trajet (id INT AUTO_INCREMENT NOT NULL, ville_depart VARCHAR(120) NOT NULL, ville_arrivee VARCHAR(120) NOT NULL, date DATE NOT NULL, heure TIME NOT NULL, distance_km INT NOT NULL, nombre_places INT NOT NULL, prix_par_place NUMERIC(6, 2) NOT NULL, vehicule VARCHAR(120) DEFAULT NULL, point_rendez_vous VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, statut VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, conducteur_id INT NOT NULL, INDEX IDX_2B5BA98CF16F4AC6 (conducteur_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reservation (id INT AUTO_INCREMENT NOT NULL, date_reservation DATETIME NOT NULL, nombre_places INT NOT NULL, prix_total NUMERIC(7, 2) NOT NULL, statut VARCHAR(255) NOT NULL, passager_id INT NOT NULL, trajet_id INT NOT NULL, UNIQUE INDEX uniq_passager_trajet (passager_id, trajet_id), INDEX IDX_42C8495571A51189 (passager_id), INDEX IDX_42C84955D12A823 (trajet_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE trajet ADD CONSTRAINT FK_2B5BA98CF16F4AC6 FOREIGN KEY (conducteur_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C8495571A51189 FOREIGN KEY (passager_id) REFERENCES utilisateur (id)');
        $this->addSql('ALTER TABLE reservation ADD CONSTRAINT FK_42C84955D12A823 FOREIGN KEY (trajet_id) REFERENCES trajet (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C84955D12A823');
        $this->addSql('ALTER TABLE reservation DROP FOREIGN KEY FK_42C8495571A51189');
        $this->addSql('ALTER TABLE trajet DROP FOREIGN KEY FK_2B5BA98CF16F4AC6');
        $this->addSql('DROP TABLE reservation');
        $this->addSql('DROP TABLE trajet');
        $this->addSql('DROP TABLE utilisateur');
    }
}
