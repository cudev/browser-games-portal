<?php

use Phinx\Migration\AbstractMigration;

class SocialNetworkConnections extends AbstractMigration
{
    public function change()
    {
        $this->table('social_network_connections')
            ->addColumn('user_id', 'integer')
            ->addColumn('remote_user_id', 'string')
            ->addColumn('name', 'string')
            ->addForeignKey('user_id', 'users', 'id', ['delete' => 'CASCADE'])
            ->addTimestamps()
            ->create();
    }
}
