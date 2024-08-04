<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\Project;
use App\Entity\TimeEntry;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class Version20240731150036 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create time_entries table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable(TimeEntry::TABLE_NAME);
        $table->addColumn('id', 'uuid');
        $table->addColumn('project_id', UlidType::NAME, ['notnull' => false]);
        $table->addColumn('date_start', Types::DATETIME_IMMUTABLE);
        $table->addColumn('date_end', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $table->addColumn('billable', Types::BOOLEAN);
        $table->addColumn('description', Types::STRING);
        $table->addColumn('status', Types::STRING);
        $table->addColumn('entry_type', Types::STRING);
        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(Project::TABLE_NAME, ['project_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable(TimeEntry::TABLE_NAME);
    }
}
