<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQL120Platform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250213043048 extends AbstractMigration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQL120Platform,
            'Migration can only be executed safely on \'postgresql\'.'
        );

        $this->addSql('CREATE SEQUENCE file_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE file (id INT NOT NULL, creator_id INT NOT NULL, name TEXT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8C9F361061220EA6 ON file (creator_id)');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F361061220EA6 FOREIGN KEY (creator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE statement ADD file_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE statement DROP filename');
        $this->addSql('ALTER TABLE statement ADD CONSTRAINT FK_C0DB517693CB796C FOREIGN KEY (file_id) REFERENCES file (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C0DB517693CB796C ON statement (file_id)');
    }

    /**
     * @noinspection PhpMissingParentCallCommonInspection
     *
     * {@inheritdoc}
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQL120Platform,
            'Migration can only be executed safely on \'postgresql\' version 12 and more.'
        );

        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE statement DROP CONSTRAINT FK_C0DB517693CB796C');
        $this->addSql('DROP SEQUENCE file_id_seq CASCADE');
        $this->addSql('ALTER TABLE file DROP CONSTRAINT FK_8C9F361061220EA6');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP INDEX UNIQ_C0DB517693CB796C');
        $this->addSql('ALTER TABLE statement ADD filename TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE statement DROP file_id');
    }
}
