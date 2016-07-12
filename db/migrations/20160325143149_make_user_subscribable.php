<?php

use Ludos\Entity\Role;
use Phinx\Migration\AbstractMigration;

class MakeUserSubscribable extends AbstractMigration
{
    private $role = Role::SUBSCRIBER;

    public function up()
    {
        $this->table('users')
            ->addColumn('enabled', 'boolean')
            ->changeColumn('password_hash', 'string', ['limit' => 255, 'null' => true])
            ->update();

        $this->insert('roles', ['name' => $this->role]);
        $this->insert('static_content', [
            ['id' => 51, 'access_key' => 'footer.newsletter.subscribed'],
            ['id' => 52, 'access_key' => 'footer.newsletter.error']
        ]);
        $this->insert('static_content_data', [
            [
                'translation' => 'Yay! We\'re glad you\'re with us!',
                'static_content_id' => 51,
                'locale_id' => 1
            ],
            [
                'translation' => 'Oops! Email seems wrong.',
                'static_content_id' => 52,
                'locale_id' => 1
            ]
        ]);
    }

    public function down()
    {
        $this->table('users')
            ->removeColumn('enabled')
            ->changeColumn('password_hash', 'string', ['limit' => 255, 'null' => false])
            ->update();

        $this->execute("DELETE FROM roles WHERE name='{$this->role}';");
        $this->execute('DELETE FROM static_content_data WHERE static_content_id IN (51, 52);');
        $this->execute('DELETE FROM static_content WHERE id IN (51, 52);');
    }
}
