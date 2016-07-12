<?php

use Phinx\Migration\AbstractMigration;

class SiteTitleTranslations extends AbstractMigration
{
    public function up()
    {

        $this->insert('static_content', [
            ['id' => 53, 'access_key' => 'site.title.default'],
            ['id' => 54, 'access_key' => 'site.title.tag']
        ]);
        $this->insert('static_content_data', [
            [
                'translation' => '{domain} - play best games!',
                'static_content_id' => 53,
                'locale_id' => 1
            ],
            [
                'translation' => '{domain} - play {tag} games!',
                'static_content_id' => 54,
                'locale_id' => 1
            ]
        ]);
    }

    public function down()
    {
        $this->execute('DELETE FROM static_content_data WHERE static_content_id IN (53, 54);');
        $this->execute('DELETE FROM static_content WHERE id IN (53, 54);');
    }
}
