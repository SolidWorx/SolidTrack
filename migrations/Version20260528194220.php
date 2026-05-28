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
use App\Entity\User;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260528194220 extends AbstractMigration
{
    /**
     * Doctrine's auto-generated unique-index name for Tag::$slug.
     */
    private const TAG_SLUG_INDEX = 'UNIQ_6FBC9426989D9B62';

    /**
     * The explicit index name originally assigned in Version20260518112707.
     */
    private const TAG_SLUG_INDEX_LEGACY = 'UNIQ_TAG_SLUG';

    #[Override]
    public function getDescription(): string
    {
        return 'Add facebook_id to users and align the tags.slug unique index name with the entity mapping';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $tags = $schema->getTable(Tag::TABLE_NAME);
        $tags->dropIndex(self::TAG_SLUG_INDEX_LEGACY);
        $tags->addUniqueIndex(['slug'], self::TAG_SLUG_INDEX);

        $users = $schema->getTable(User::TABLE_NAME);
        $users->addColumn('facebook_id', Types::STRING, [
            'length' => 45,
            'notnull' => false,
        ]);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $tags = $schema->getTable(Tag::TABLE_NAME);
        $tags->dropIndex(self::TAG_SLUG_INDEX);
        $tags->addUniqueIndex(['slug'], self::TAG_SLUG_INDEX_LEGACY);

        $users = $schema->getTable(User::TABLE_NAME);
        $users->dropColumn('facebook_id');
    }
}
