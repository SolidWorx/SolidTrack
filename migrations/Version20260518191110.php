<?php

declare(strict_types=1);

/*
 * This file is part of SolidTrack project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace DoctrineMigrations;

use App\Entity\Client;
use App\Entity\Project;
use App\Entity\TimeEntry;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260518191110 extends AbstractMigration
{
    private const PROJECT_CLIENT_FK = 'FK_5C93B3A419EB6921';

    private const TIME_ENTRY_PROJECT_FK = 'FK_797F12A3166D1F9C';

    #[Override]
    public function getDescription(): string
    {
        return 'Cascade delete from clients to projects and from projects to time entries';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $projects = $schema->getTable(Project::TABLE_NAME);
        $projects->removeForeignKey(self::PROJECT_CLIENT_FK);
        $projects->addForeignKeyConstraint(
            Client::TABLE_NAME,
            ['client_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            self::PROJECT_CLIENT_FK,
        );

        $timeEntries = $schema->getTable(TimeEntry::TABLE_NAME);
        $timeEntries->removeForeignKey(self::TIME_ENTRY_PROJECT_FK);
        $timeEntries->addForeignKeyConstraint(
            Project::TABLE_NAME,
            ['project_id'],
            ['id'],
            ['onDelete' => 'CASCADE'],
            self::TIME_ENTRY_PROJECT_FK,
        );
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $projects = $schema->getTable(Project::TABLE_NAME);
        $projects->removeForeignKey(self::PROJECT_CLIENT_FK);
        $projects->addForeignKeyConstraint(
            Client::TABLE_NAME,
            ['client_id'],
            ['id'],
            [],
            self::PROJECT_CLIENT_FK,
        );

        $timeEntries = $schema->getTable(TimeEntry::TABLE_NAME);
        $timeEntries->removeForeignKey(self::TIME_ENTRY_PROJECT_FK);
        $timeEntries->addForeignKeyConstraint(
            Project::TABLE_NAME,
            ['project_id'],
            ['id'],
            [],
            self::TIME_ENTRY_PROJECT_FK,
        );
    }
}
