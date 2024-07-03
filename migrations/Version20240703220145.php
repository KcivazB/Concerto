<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240703220145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magic_link DROP FOREIGN KEY FK_6B40B1C6A76ED395');
        $this->addSql('ALTER TABLE magic_link ADD CONSTRAINT FK_6B40B1C6A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE magic_link DROP FOREIGN KEY FK_6B40B1C6A76ED395');
        $this->addSql('ALTER TABLE magic_link ADD CONSTRAINT FK_6B40B1C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }
}
