<?php

use Phinx\Migration\AbstractMigration;

class AdditionalSeoControl extends AbstractMigration
{
    public function up()
    {
        $this->table('locales')
            ->removeColumn('country')
            ->addColumn('title', 'string', ['null' => true])
            ->addColumn('description', 'string', ['null' => true])
            ->addColumn('contact_email', 'string', ['null' => true])
            ->update();

        $this->table('tag_names')
            ->addColumn('description', 'string', ['null' => true])
            ->update();
    }

    public function down()
    {
        $this->table('locales')
            ->addColumn('country', 'string', ['null' => true])
            ->removeColumn('title')
            ->removeColumn('description')
            ->removeColumn('contact_email')
            ->update();

        $this->table('tag_names')
            ->removeColumn('description')
            ->update();
    }
}
