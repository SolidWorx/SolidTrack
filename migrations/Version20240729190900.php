<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Client;
use App\Entity\Project;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class Version20240729190900 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create client and project tables';
    }

    public function up(Schema $schema): void
    {
        $clientsTable = $schema->createTable(Client::TABLE_NAME);
        $clientsTable->addColumn('id', UlidType::NAME);
        $clientsTable->addColumn('name', Types::STRING);
        $clientsTable->addColumn('currency', Types::STRING, ['length' => 3, 'notnull' => false]);
        $clientsTable->setPrimaryKey(['id']);

        $projectsTable = $schema->createTable(Project::TABLE_NAME);
        $projectsTable->addColumn('id', UlidType::NAME);
        $projectsTable->addColumn('client_id', UlidType::NAME);
        $projectsTable->addColumn('name', Types::STRING);
        $projectsTable->addColumn('hourly_rate', Types::FLOAT, ['notnull' => false]);
        $projectsTable->setPrimaryKey(['id']);
        $projectsTable->addForeignKeyConstraint(Client::TABLE_NAME, ['client_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable(Client::TABLE_NAME);
        $schema->dropTable(Project::TABLE_NAME);
    }
}
