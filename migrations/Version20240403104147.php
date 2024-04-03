<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240403104147 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE job ADD name VARCHAR(255) NOT NULL, ADD should_be_finished DATETIME NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FBD8E0F85E237E06 ON job (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_FBD8E0F85E237E06 ON job');
        $this->addSql('ALTER TABLE job DROP name, DROP should_be_finished');
    }
}
