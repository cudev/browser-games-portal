<?php

use Phinx\Migration\AbstractMigration;

class PlayerSubscriptionTranslations extends AbstractMigration
{
    public function up()
    {
        $this->insert('static_content', [
            ['id' => 73, 'access_key' => 'player.cover.subscribe.headline'],
            ['id' => 74, 'access_key' => 'player.cover.subscribe.tagline'],
            ['id' => 75, 'access_key' => 'player.cover.subscribe.skip'],
        ]);
        $this->insert('static_content_data', [
            [
                'translation' => 'Join us!',
                'static_content_id' => 73,
                'locale_id' => 1
            ],
            [
                'translation' => 'Be first to hear about our new games',
                'static_content_id' => 74,
                'locale_id' => 1
            ],
            [
                'translation' => 'proceed to game',
                'static_content_id' => 75,
                'locale_id' => 1
            ]
        ]);
    }

    public function down()
    {
        $this->execute('DELETE FROM static_content_data WHERE static_content_id IN (73, 74, 75);');
        $this->execute('DELETE FROM static_content WHERE id  IN (73, 74, 75);');
    }
}
