<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240319155859 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE `category` (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, name VARCHAR(255) NOT NULL, position INT NOT NULL, INDEX IDX_64C19C17E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `note` (id INT AUTO_INCREMENT NOT NULL, priority_id INT NOT NULL, state_id INT DEFAULT NULL, owner_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, visibility VARCHAR(20) NOT NULL, type VARCHAR(5) NOT NULL, due_date DATE DEFAULT NULL, INDEX IDX_CFBDFA14497B19F9 (priority_id), INDEX IDX_CFBDFA145D83CC1 (state_id), INDEX IDX_CFBDFA147E3C61F9 (owner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE note_category (note_id INT NOT NULL, category_id INT NOT NULL, INDEX IDX_1617C55F26ED0855 (note_id), INDEX IDX_1617C55F12469DE2 (category_id), PRIMARY KEY(note_id, category_id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `priority` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `state` (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, position INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `subtask` (id INT AUTO_INCREMENT NOT NULL, parent_note_id INT NOT NULL, label VARCHAR(255) NOT NULL, done TINYINT(1) NOT NULL, position INT NOT NULL, INDEX IDX_8BCBA9AE54A57C13 (parent_note_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(255) NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME (username), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `category` ADD CONSTRAINT FK_64C19C17E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `note` ADD CONSTRAINT FK_CFBDFA14497B19F9 FOREIGN KEY (priority_id) REFERENCES `priority` (id)');
        $this->addSql('ALTER TABLE `note` ADD CONSTRAINT FK_CFBDFA145D83CC1 FOREIGN KEY (state_id) REFERENCES `state` (id)');
        $this->addSql('ALTER TABLE `note` ADD CONSTRAINT FK_CFBDFA147E3C61F9 FOREIGN KEY (owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE note_category ADD CONSTRAINT FK_1617C55F26ED0855 FOREIGN KEY (note_id) REFERENCES `note` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE note_category ADD CONSTRAINT FK_1617C55F12469DE2 FOREIGN KEY (category_id) REFERENCES `category` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE `subtask` ADD CONSTRAINT FK_8BCBA9AE54A57C13 FOREIGN KEY (parent_note_id) REFERENCES `note` (id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE `category` DROP FOREIGN KEY FK_64C19C17E3C61F9');
        $this->addSql('ALTER TABLE `note` DROP FOREIGN KEY FK_CFBDFA14497B19F9');
        $this->addSql('ALTER TABLE `note` DROP FOREIGN KEY FK_CFBDFA145D83CC1');
        $this->addSql('ALTER TABLE `note` DROP FOREIGN KEY FK_CFBDFA147E3C61F9');
        $this->addSql('ALTER TABLE note_category DROP FOREIGN KEY FK_1617C55F26ED0855');
        $this->addSql('ALTER TABLE note_category DROP FOREIGN KEY FK_1617C55F12469DE2');
        $this->addSql('ALTER TABLE `subtask` DROP FOREIGN KEY FK_8BCBA9AE54A57C13');
        $this->addSql('DROP TABLE `category`');
        $this->addSql('DROP TABLE `note`');
        $this->addSql('DROP TABLE note_category');
        $this->addSql('DROP TABLE `priority`');
        $this->addSql('DROP TABLE `state`');
        $this->addSql('DROP TABLE `subtask`');
        $this->addSql('DROP TABLE `user`');
    }
}
