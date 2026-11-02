<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261101025536 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE albums (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, is_seed TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX idx_albums_sandbox_cleanup (is_seed, created_at), INDEX IDX_F4E2474FA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE comments (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, body LONGTEXT NOT NULL, is_seed TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, post_id INT NOT NULL, INDEX idx_comments_sandbox_cleanup (is_seed, created_at), INDEX IDX_5F9E962A4B89032C (post_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE photos (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(500) DEFAULT NULL, thumbnail_url VARCHAR(500) DEFAULT NULL, is_seed TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, album_id INT NOT NULL, INDEX idx_photos_sandbox_cleanup (is_seed, created_at), INDEX IDX_876E0D91137ABCF (album_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE posts (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, body LONGTEXT NOT NULL, is_seed TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX idx_posts_sandbox_cleanup (is_seed, created_at), INDEX IDX_885DBAFAA76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE todos (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, completed TINYINT DEFAULT 0 NOT NULL, is_seed TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, user_id INT NOT NULL, INDEX idx_todos_sandbox_cleanup (is_seed, created_at), INDEX IDX_CD826255A76ED395 (user_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, username VARCHAR(255) DEFAULT NULL, email VARCHAR(255) DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, is_seed TINYINT DEFAULT 0 NOT NULL, created_at DATETIME NOT NULL, address_street VARCHAR(255) DEFAULT NULL, address_suite VARCHAR(255) DEFAULT NULL, address_city VARCHAR(255) DEFAULT NULL, address_zipcode VARCHAR(32) DEFAULT NULL, address_geo_lat VARCHAR(32) DEFAULT NULL, address_geo_lng VARCHAR(32) DEFAULT NULL, company_name VARCHAR(255) DEFAULT NULL, company_catch_phrase VARCHAR(255) DEFAULT NULL, company_bs VARCHAR(255) DEFAULT NULL, INDEX idx_users_sandbox_cleanup (is_seed, created_at), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id))');
        $this->addSql('ALTER TABLE albums ADD CONSTRAINT FK_F4E2474FA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962A4B89032C FOREIGN KEY (post_id) REFERENCES posts (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE photos ADD CONSTRAINT FK_876E0D91137ABCF FOREIGN KEY (album_id) REFERENCES albums (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE todos ADD CONSTRAINT FK_CD826255A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE albums DROP FOREIGN KEY FK_F4E2474FA76ED395');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962A4B89032C');
        $this->addSql('ALTER TABLE photos DROP FOREIGN KEY FK_876E0D91137ABCF');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFAA76ED395');
        $this->addSql('ALTER TABLE todos DROP FOREIGN KEY FK_CD826255A76ED395');
        $this->addSql('DROP TABLE albums');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE photos');
        $this->addSql('DROP TABLE posts');
        $this->addSql('DROP TABLE todos');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
