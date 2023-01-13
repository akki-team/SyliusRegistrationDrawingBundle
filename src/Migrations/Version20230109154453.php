<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230109154453 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE registration_drawing (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, format VARCHAR(255) NOT NULL, delimiter VARCHAR(255) DEFAULT NULL, periodicity VARCHAR(255) NOT NULL, day VARCHAR(255) NOT NULL, send_mode VARCHAR(255) NOT NULL, deposit_address VARCHAR(255) NOT NULL, user VARCHAR(255) NOT NULL, ssh_key VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_drawing_field (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, name VARCHAR(255) NOT NULL, equivalent VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B903933C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_drawing_field_association (id INT AUTO_INCREMENT NOT NULL, drawing_id INT NOT NULL, `order` INT DEFAULT NULL, position INT DEFAULT NULL, length INT DEFAULT NULL, format VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_drawing_output_formats (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, format VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE registration_drawing_field ADD CONSTRAINT FK_B903933C54C8C93 FOREIGN KEY (type_id) REFERENCES registration_drawing_output_formats (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_drawing_field DROP FOREIGN KEY FK_B903933C54C8C93');
        $this->addSql('DROP TABLE registration_drawing');
        $this->addSql('DROP TABLE registration_drawing_field');
        $this->addSql('DROP TABLE registration_drawing_field_association');
        $this->addSql('DROP TABLE registration_drawing_output_formats');
    }
}
