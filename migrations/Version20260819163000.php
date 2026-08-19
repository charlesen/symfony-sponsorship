<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260819163000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add points, referral clicks, registration IP and unique referrer code index to user table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` 
            ADD points INT NOT NULL DEFAULT 0, 
            ADD referral_clicks INT NOT NULL DEFAULT 0, 
            ADD registration_ip VARCHAR(64) DEFAULT NULL
        SQL);
        $this->addSql(<<<'SQL'
            CREATE UNIQUE INDEX UNIQ_REFERRER_CODE ON `user` (referrer_code)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            DROP INDEX UNIQ_REFERRER_CODE ON `user`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE `user` 
            DROP points, 
            DROP referral_clicks, 
            DROP registration_ip
        SQL);
    }
}
