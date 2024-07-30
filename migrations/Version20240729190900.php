<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240729190900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create client and project tables';
    }

    public function up(Schema $schema): void
    {
        $clientsTable = $schema->createTable('client');
        $clientsTable->addColumn('id', 'ulid');
        $clientsTable->addColumn('name', 'string');
        $clientsTable->addColumn('currency', 'string', ['length' => 3, 'notnull' => false]);
        $clientsTable->setPrimaryKey(['id']);

        $projectsTable = $schema->createTable('project');
        $projectsTable->addColumn('id', 'ulid');
        $projectsTable->addColumn('client_id', 'ulid');
        $projectsTable->addColumn('hourly_rate', 'float', ['notnull' => false]);
        $projectsTable->setPrimaryKey(['id']);
        $projectsTable->addForeignKeyConstraint('client', ['client_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('client');
        $schema->dropTable('project');
    }
}
