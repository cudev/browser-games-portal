<?php

use Phinx\Migration\AbstractMigration;

class AccountTranslations extends AbstractMigration
{
    public function up()
    {
        $this->insert('static_content', [
            ['id' => 66, 'access_key' => 'account.age'],
            ['id' => 67, 'access_key' => 'games.my.bookmarked'],
            ['id' => 68, 'access_key' => 'games.my.last']
        ]);
        $this->insert('static_content_data', [
            [
                'translation' => 'Age',
                'static_content_id' => 66,
                'locale_id' => 1
            ],
            [
                'translation' => 'My bookmarked',
                'static_content_id' => 67,
                'locale_id' => 1
            ],
            [
                'translation' => 'My last games',
                'static_content_id' => 68,
                'locale_id' => 1
            ]
        ]);
        $this->execute("UPDATE static_content_data SET `translation`='Save profile' WHERE locale_id=1 AND static_content_id=45");
        $this->execute("UPDATE static_content_data SET `translation`='Edit profile' WHERE locale_id=1 AND static_content_id=46");
    }

    public function down()
    {
        $this->execute('DELETE FROM static_content_data WHERE static_content_id BETWEEN 66 AND 68;');
        $this->execute('DELETE FROM static_content WHERE id BETWEEN 66 AND 68');
    }
}
