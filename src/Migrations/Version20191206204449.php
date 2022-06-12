<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191206204449 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE costs (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, document VARCHAR(255) DEFAULT NULL, value DOUBLE PRECISION NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE drivers (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(124) NOT NULL, firstname VARCHAR(124) NOT NULL, city VARCHAR(124) DEFAULT NULL, code_post VARCHAR(10) DEFAULT NULL, street VARCHAR(124) DEFAULT NULL, phone VARCHAR(12) DEFAULT NULL, email VARCHAR(124) DEFAULT NULL, description VARCHAR(200) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE records (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT NOT NULL, driver_id INT NOT NULL, month INT DEFAULT NULL, year INT DEFAULT NULL, INDEX IDX_9C9D5846545317D1 (vehicle_id), INDEX IDX_9C9D5846C3423909 (driver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE route (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, km INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE settings (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, street VARCHAR(255) NOT NULL, code_post VARCHAR(20) NOT NULL, city VARCHAR(100) NOT NULL, nip VARCHAR(24) NOT NULL, regon VARCHAR(24) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(64) NOT NULL, email VARCHAR(255) NOT NULL, active TINYINT(1) NOT NULL, password VARCHAR(128) NOT NULL, phone VARCHAR(24) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_notify (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date DATETIME NOT NULL, ip VARCHAR(50) NOT NULL, text LONGTEXT NOT NULL, INDEX IDX_4429F6F5A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vehicles (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, registration VARCHAR(25) NOT NULL, vin VARCHAR(25) NOT NULL, first_registration VARCHAR(25) DEFAULT NULL, type VARCHAR(50) DEFAULT NULL, date_overview DATE DEFAULT NULL, date_insurance DATE DEFAULT NULL, date_oil DATE DEFAULT NULL, date_warranty DATE DEFAULT NULL, date_udt DATE DEFAULT NULL, date_mechanic DATE DEFAULT NULL, date_documents DATE DEFAULT NULL, description LONGTEXT DEFAULT NULL, updated_at DATETIME NOT NULL, document_name VARCHAR(255) DEFAULT NULL, document_original_name VARCHAR(255) DEFAULT NULL, document_mime_type VARCHAR(255) DEFAULT NULL, document_size INT DEFAULT NULL, document_dimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE records ADD CONSTRAINT FK_9C9D5846545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)');
        $this->addSql('ALTER TABLE records ADD CONSTRAINT FK_9C9D5846C3423909 FOREIGN KEY (driver_id) REFERENCES drivers (id)');
        $this->addSql('ALTER TABLE user_notify ADD CONSTRAINT FK_4429F6F5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE records DROP FOREIGN KEY FK_9C9D5846C3423909');
        $this->addSql('ALTER TABLE user_notify DROP FOREIGN KEY FK_4429F6F5A76ED395');
        $this->addSql('ALTER TABLE records DROP FOREIGN KEY FK_9C9D5846545317D1');
        $this->addSql('DROP TABLE costs');
        $this->addSql('DROP TABLE drivers');
        $this->addSql('DROP TABLE records');
        $this->addSql('DROP TABLE route');
        $this->addSql('DROP TABLE settings');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_notify');
        $this->addSql('DROP TABLE vehicles');
    }
}
