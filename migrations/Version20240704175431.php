<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240704175431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status DROP FOREIGN KEY FK_7B00651C166D1F9C');
        $this->addSql('DROP INDEX IDX_7B00651C166D1F9C ON status');
        $this->addSql('ALTER TABLE status DROP project_id');
        $this->addSql('ALTER TABLE tag ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B7837E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_389B7837E3C61F9 ON tag (owner_id)');
        $this->addSql('ALTER TABLE tag_category DROP FOREIGN KEY FK_307D621A7E3C61F9');
        $this->addSql('DROP INDEX IDX_307D621A7E3C61F9 ON tag_category');
        $this->addSql('ALTER TABLE tag_category DROP owner_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE status ADD project_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE status ADD CONSTRAINT FK_7B00651C166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_7B00651C166D1F9C ON status (project_id)');
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B7837E3C61F9');
        $this->addSql('DROP INDEX IDX_389B7837E3C61F9 ON tag');
        $this->addSql('ALTER TABLE tag DROP owner_id');
        $this->addSql('ALTER TABLE tag_category ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tag_category ADD CONSTRAINT FK_307D621A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_307D621A7E3C61F9 ON tag_category (owner_id)');
    }
}
