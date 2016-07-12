<?php

use Phinx\Migration\AbstractMigration;

class MyPlayedBookmarkedEmptyTranslations extends AbstractMigration
{
    public function up()
    {
        $this->insert('static_content', [
            ['id' => 69, 'access_key' => 'games.my.bookmarked.empty'],
            ['id' => 70, 'access_key' => 'games.my.last.empty'],
        ]);
        $this->insert('static_content_data', [
            [
                'translation' => 'You haven\'t yet bookmarked any game.',
                'static_content_id' => 69,
                'locale_id' => 1
            ],
            [
                'translation' => 'You haven\'t yet played any game.',
                'static_content_id' => 70,
                'locale_id' => 1
            ]
        ]);
    }

    public function down()
    {
        $this->execute('DELETE FROM static_content_data WHERE static_content_id IN (69, 70);');
        $this->execute('DELETE FROM static_content WHERE id  IN (69, 70);');
    }
}
