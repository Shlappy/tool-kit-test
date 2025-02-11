<?php

declare(strict_types=1);

// phpcs:ignoreFile
namespace <namespace>;

use Doctrine\DBAL\Platforms\PostgreSQL120Platform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class <className> extends AbstractMigration
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

<up>
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

<down>
    }
}
