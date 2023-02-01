<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20230201132617 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE registration_drawing (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, format VARCHAR(255) NOT NULL, delimiter VARCHAR(255) DEFAULT NULL, periodicity VARCHAR(255) NOT NULL, day VARCHAR(255) NOT NULL, send_mode VARCHAR(255) NOT NULL, deposit_address VARCHAR(255) NOT NULL, user VARCHAR(255) NOT NULL, host VARCHAR(255) NOT NULL, port INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_drawing_field (id INT AUTO_INCREMENT NOT NULL, type_id INT NOT NULL, name VARCHAR(255) NOT NULL, equivalent VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, INDEX IDX_B903933C54C8C93 (type_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_drawing_field_association (id INT AUTO_INCREMENT NOT NULL, drawing_id INT NOT NULL, field_id INT NOT NULL, ordre INT DEFAULT NULL, position INT DEFAULT NULL, length INT DEFAULT NULL, format VARCHAR(255) DEFAULT NULL, selection VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE registration_drawing_output_formats (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(255) NOT NULL, format VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET UTF8 COLLATE `UTF8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE registration_drawing_field ADD CONSTRAINT FK_B903933C54C8C93 FOREIGN KEY (type_id) REFERENCES registration_drawing_output_formats (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE odiseo_vendor ADD registration_drawing_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE odiseo_vendor ADD CONSTRAINT FK_B506F54FDB6EE4A0 FOREIGN KEY (registration_drawing_id) REFERENCES registration_drawing (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B506F54FDB6EE4A0 ON odiseo_vendor (registration_drawing_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE odiseo_vendor DROP FOREIGN KEY FK_B506F54FDB6EE4A0');
        $this->addSql('ALTER TABLE registration_drawing_field DROP FOREIGN KEY FK_B903933C54C8C93');
        $this->addSql('DROP TABLE registration_drawing');
        $this->addSql('DROP TABLE registration_drawing_field');
        $this->addSql('DROP TABLE registration_drawing_field_association');
        $this->addSql('DROP TABLE registration_drawing_output_formats');
        $this->addSql('DROP INDEX IDX_B506F54FDB6EE4A0 ON odiseo_vendor');
        $this->addSql('ALTER TABLE odiseo_vendor DROP registration_drawing_id');
    }
}
