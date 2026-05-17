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

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260517140715 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Allow time_entries.description to be null';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $schema->getTable('time_entries')
            ->modifyColumn('description', ['notnull' => false]);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $schema->getTable('time_entries')
            ->modifyColumn('description', ['notnull' => true]);
    }
}
