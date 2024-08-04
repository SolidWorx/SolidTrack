<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\TimeEntry;
use App\Entity\User;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class Version20240803182827 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add user to time_entries';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable(TimeEntry::TABLE_NAME);
        $table->addColumn('user_id', UlidType::NAME, ['notnull' => true]);
        $table->addForeignKeyConstraint(User::TABLE_NAME, ['user_id'], ['id']);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable(TimeEntry::TABLE_NAME);
        $table->dropColumn('user_id');
    }
}
