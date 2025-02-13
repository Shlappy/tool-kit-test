<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQL120Platform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250212164057 extends AbstractMigration
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

        $this->addSql('CREATE SEQUENCE statement_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE statement (id INT NOT NULL, creator_id INT NOT NULL, number VARCHAR(255) NOT NULL, date DATE DEFAULT NULL, full_name VARCHAR(500) NOT NULL, file TEXT DEFAULT NULL, comment TEXT DEFAULT NULL, phone VARCHAR(255) DEFAULT NULL, type_id INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_C0DB517661220EA6 ON statement (creator_id)');
        $this->addSql('ALTER TABLE statement ADD CONSTRAINT FK_C0DB517661220EA6 FOREIGN KEY (creator_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
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
        $this->addSql('DROP SEQUENCE statement_id_seq CASCADE');
        $this->addSql('ALTER TABLE statement DROP CONSTRAINT FK_C0DB517661220EA6');
        $this->addSql('DROP TABLE statement');
    }
}
