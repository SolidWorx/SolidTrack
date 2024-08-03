<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use App\Entity\User;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\Types\UlidType;

final class Version20240729173616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table';
    }

    public function up(Schema $schema): void
    {
        $users = $schema->createTable(User::TABLE_NAME);
        $users->addColumn('id', UlidType::NAME);
        $users->addColumn('email', Types::STRING, ['length' => 180]);
        $users->addColumn('roles', Types::JSON);
        $users->addColumn('password', Types::STRING);
        $users->setPrimaryKey(['id']);
        $users->addUniqueIndex(['email'], 'UNIQ_IDENTIFIER_EMAIL');

        $messengerMessages = $schema->createTable('messenger_messages');
        $messengerMessages->addColumn('id', Types::BIGINT, ['autoincrement' => true]);
        $messengerMessages->addColumn('body', Types::TEXT);
        $messengerMessages->addColumn('headers', Types::TEXT);
        $messengerMessages->addColumn('queue_name', Types::STRING, ['length' => 190]);
        $messengerMessages->addColumn('created_at', Types::DATETIME_IMMUTABLE);
        $messengerMessages->addColumn('available_at', Types::DATETIME_IMMUTABLE);
        $messengerMessages->addColumn('delivered_at', Types::DATETIME_IMMUTABLE, ['notnull' => false]);
        $messengerMessages->setPrimaryKey(['id']);
        $messengerMessages->addIndex(['queue_name']);
        $messengerMessages->addIndex(['available_at']);
        $messengerMessages->addIndex(['delivered_at']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable(User::TABLE_NAME);
        $schema->dropTable('messenger_messages');
    }
}
