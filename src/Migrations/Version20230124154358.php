<?php

declare(strict_types=1);

namespace Akki\SyliusRegistrationDrawingBundle\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230124154358 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE odiseo_vendor ADD registration_drawing_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE odiseo_vendor ADD CONSTRAINT FK_B506F54FDB6EE4A0 FOREIGN KEY (registration_drawing_id) REFERENCES registration_drawing (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_B506F54FDB6EE4A0 ON odiseo_vendor (registration_drawing_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE odiseo_vendor DROP FOREIGN KEY FK_B506F54FDB6EE4A0');
        $this->addSql('DROP INDEX IDX_B506F54FDB6EE4A0 ON odiseo_vendor');
        $this->addSql('ALTER TABLE odiseo_vendor DROP registration_drawing_id');
    }
}
