<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240529191500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add created_at and updated_at to user_assignment table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_assignment ADD created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'(DC2Type:datetime_immutable)\', ADD updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE user_assignment DROP created_at, DROP updated_at');
    }
}
