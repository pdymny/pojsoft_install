<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20191206222905 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE vehicles_attachments (id INT AUTO_INCREMENT NOT NULL, vehicle_id INT DEFAULT NULL, image VARCHAR(200) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_89555552545317D1 (vehicle_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vehicles_attachments ADD CONSTRAINT FK_89555552545317D1 FOREIGN KEY (vehicle_id) REFERENCES vehicles (id)');
        $this->addSql('ALTER TABLE vehicles DROP document_name, DROP document_original_name, DROP document_mime_type, DROP document_size, DROP document_dimensions');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE vehicles_attachments');
        $this->addSql('ALTER TABLE vehicles ADD document_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD document_original_name VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD document_mime_type VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, ADD document_size INT DEFAULT NULL, ADD document_dimensions LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:simple_array)\'');
    }
}
