<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240729173616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users table';
    }

    public function up(Schema $schema): void
    {
        $users = $schema->createTable('users');
        $users->addColumn('id', 'uuid');
        $users->addColumn('email', 'string', ['length' => 180]);
        $users->addColumn('roles', 'json');
        $users->addColumn('password', 'string');
        $users->setPrimaryKey(['id']);
        $users->addUniqueIndex(['email'], 'UNIQ_IDENTIFIER_EMAIL');

        $messengerMessages = $schema->createTable('messenger_messages');
        $messengerMessages->addColumn('id', 'bigint', ['autoincrement' => true]);
        $messengerMessages->addColumn('body', 'text');
        $messengerMessages->addColumn('headers', 'text');
        $messengerMessages->addColumn('queue_name', 'string', ['length' => 190]);
        $messengerMessages->addColumn('created_at', 'datetime');
        $messengerMessages->addColumn('available_at', 'datetime');
        $messengerMessages->addColumn('delivered_at', 'datetime', ['notnull' => false]);
        $messengerMessages->setPrimaryKey(['id']);
        $messengerMessages->addIndex(['queue_name']);
        $messengerMessages->addIndex(['available_at']);
        $messengerMessages->addIndex(['delivered_at']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('users');
        $schema->dropTable('messenger_messages');
    }
}
