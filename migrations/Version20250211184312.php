<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQL120Platform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250211184312 extends AbstractMigration
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

        $this->addSql('ALTER TABLE "user" ALTER birth_date DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER address DROP NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER phone DROP NOT NULL');
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
        $this->addSql('ALTER TABLE "user" ALTER birth_date SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER address SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER phone SET NOT NULL');
    }
}
