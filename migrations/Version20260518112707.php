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

use App\Entity\Tag;
use App\Entity\TimeEntry;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Override;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class Version20260518112707 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Create tags table and time_entry_tags join table';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $tags = $schema->createTable(Tag::TABLE_NAME);
        $tags->addColumn('id', UlidType::NAME);
        $tags->addColumn('name', Types::STRING, ['length' => 100]);
        $tags->addColumn('slug', Types::STRING, ['length' => 100]);
        $tags->addColumn('color', Types::STRING, ['length' => 7]);
        $tags->setPrimaryKey(['id']);
        $tags->addUniqueIndex(['slug'], 'UNIQ_TAG_SLUG');

        $join = $schema->createTable('time_entry_tags');
        $join->addColumn('time_entry_id', UlidType::NAME);
        $join->addColumn('tag_id', UlidType::NAME);
        $join->setPrimaryKey(['time_entry_id', 'tag_id']);
        $join->addIndex(['time_entry_id']);
        $join->addIndex(['tag_id']);
        $join->addForeignKeyConstraint(TimeEntry::TABLE_NAME, ['time_entry_id'], ['id'], ['onDelete' => 'CASCADE']);
        $join->addForeignKeyConstraint(Tag::TABLE_NAME, ['tag_id'], ['id'], ['onDelete' => 'CASCADE']);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $schema->dropTable('time_entry_tags');
        $schema->dropTable(Tag::TABLE_NAME);
    }
}
