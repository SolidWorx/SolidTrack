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

use App\Entity\Project;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Override;

final class Version20260518135940 extends AbstractMigration
{
    #[Override]
    public function getDescription(): string
    {
        return 'Add color column to projects';
    }

    #[Override]
    public function up(Schema $schema): void
    {
        $table = $schema->getTable(Project::TABLE_NAME);
        $table->addColumn('color', Types::STRING, [
            'length' => 7,
            'default' => Project::DEFAULT_COLOR,
        ]);
    }

    #[Override]
    public function down(Schema $schema): void
    {
        $table = $schema->getTable(Project::TABLE_NAME);
        $table->dropColumn('color');
    }
}
