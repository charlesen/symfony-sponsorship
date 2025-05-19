<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250519073855 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_assignment (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, assignment_id INT NOT NULL, validated_by_id INT DEFAULT NULL, status VARCHAR(20) NOT NULL, started_at DATETIME NOT NULL, completed_at DATETIME DEFAULT NULL, data JSON DEFAULT NULL, points_earned INT NOT NULL, validated TINYINT(1) NOT NULL, validated_at DATETIME DEFAULT NULL, INDEX IDX_97A28256A76ED395 (user_id), INDEX IDX_97A28256D19302F8 (assignment_id), INDEX IDX_97A28256C69DE5E5 (validated_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_assignment ADD CONSTRAINT FK_97A28256A76ED395 FOREIGN KEY (user_id) REFERENCES `user` (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_assignment ADD CONSTRAINT FK_97A28256D19302F8 FOREIGN KEY (assignment_id) REFERENCES assignment (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_assignment ADD CONSTRAINT FK_97A28256C69DE5E5 FOREIGN KEY (validated_by_id) REFERENCES `user` (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE user_assignment DROP FOREIGN KEY FK_97A28256A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_assignment DROP FOREIGN KEY FK_97A28256D19302F8
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user_assignment DROP FOREIGN KEY FK_97A28256C69DE5E5
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user_assignment
        SQL);
    }
}
