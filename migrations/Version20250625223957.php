<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250625223957 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE country (id INT AUTO_INCREMENT NOT NULL, code VARCHAR(10) NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE guide (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, photo_url VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, is_active TINYINT(1) NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_CA9EC735E7927C74 (email), INDEX IDX_CA9EC735F92F3E70 (country_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE visite (id INT AUTO_INCREMENT NOT NULL, country_id INT DEFAULT NULL, assigned_guide_id INT DEFAULT NULL, photo_url VARCHAR(255) DEFAULT NULL, place_to_visit VARCHAR(255) NOT NULL, visit_date DATE NOT NULL, start_time TIME NOT NULL, duration DOUBLE PRECISION NOT NULL, end_time TIME NOT NULL, visit_comment LONGTEXT DEFAULT NULL, status VARCHAR(20) DEFAULT 'not_scheduled' NOT NULL, updated_at DATETIME DEFAULT NULL, INDEX IDX_B09C8CBBF92F3E70 (country_id), INDEX IDX_B09C8CBBAAE01B42 (assigned_guide_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE visitor (id INT AUTO_INCREMENT NOT NULL, first_name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL, email VARCHAR(180) NOT NULL, identity_number VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_CAE5E19FE7927C74 (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE visitor_participation (id INT AUTO_INCREMENT NOT NULL, visite_id INT NOT NULL, visitor_id INT NOT NULL, present TINYINT(1) NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_576FE3D9C1C5DC59 (visite_id), INDEX IDX_576FE3D970BEE6D (visitor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE guide ADD CONSTRAINT FK_CA9EC735F92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBBF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visite ADD CONSTRAINT FK_B09C8CBBAAE01B42 FOREIGN KEY (assigned_guide_id) REFERENCES guide (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visitor_participation ADD CONSTRAINT FK_576FE3D9C1C5DC59 FOREIGN KEY (visite_id) REFERENCES visite (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visitor_participation ADD CONSTRAINT FK_576FE3D970BEE6D FOREIGN KEY (visitor_id) REFERENCES visitor (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE guide DROP FOREIGN KEY FK_CA9EC735F92F3E70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBBF92F3E70
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visite DROP FOREIGN KEY FK_B09C8CBBAAE01B42
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visitor_participation DROP FOREIGN KEY FK_576FE3D9C1C5DC59
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE visitor_participation DROP FOREIGN KEY FK_576FE3D970BEE6D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE country
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE guide
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE visite
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE visitor
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE visitor_participation
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
