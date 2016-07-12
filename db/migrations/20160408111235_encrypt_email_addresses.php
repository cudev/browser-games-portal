<?php

use Phinx\Migration\AbstractMigration;

class EncryptEmailAddresses extends AbstractMigration
{
    public function up()
    {
        $this->table('users')
            ->addColumn('email_confirmed', 'boolean', ['default' => false])
            ->update();
    }

    public function down()
    {
        $this->table('users')
            ->removeColumn('email_confirmed')
            ->update();
    }
}
