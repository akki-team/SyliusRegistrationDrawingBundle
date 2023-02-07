<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230207150425 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_drawing ADD currency_format VARCHAR(255) DEFAULT NULL, ADD currency_delimiter VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE registration_drawing_field_association ADD name VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE registration_drawing DROP currency_format, DROP currency_delimiter');
        $this->addSql('ALTER TABLE registration_drawing_field_association DROP name');
    }
}
