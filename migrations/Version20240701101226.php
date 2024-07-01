<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240701101226 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE tag_category (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, color VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE tag ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tag ADD CONSTRAINT FK_389B78312469DE2 FOREIGN KEY (category_id) REFERENCES tag_category (id)');
        $this->addSql('CREATE INDEX IDX_389B78312469DE2 ON tag (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE tag DROP FOREIGN KEY FK_389B78312469DE2');
        $this->addSql('DROP TABLE tag_category');
        $this->addSql('DROP INDEX IDX_389B78312469DE2 ON tag');
        $this->addSql('ALTER TABLE tag DROP category_id');
    }
}
