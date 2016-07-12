<?php

use Phinx\Migration\AbstractMigration;

class AccountErrorTranslations extends AbstractMigration
{
    public function up()
    {
        $this->insert('static_content', [
            ['id' => 71, 'access_key' => 'account.picture.upload.large'],
            ['id' => 72, 'access_key' => 'account.name.error.length'],
        ]);
        $this->insert('static_content_data', [
            [
                'translation' => 'Ouch! Image size must be less than 2 Mb',
                'static_content_id' => 71,
                'locale_id' => 1
            ],
            [
                'translation' => 'Must contain from 3 up to 30 characters',
                'static_content_id' => 72,
                'locale_id' => 1
            ]
        ]);
    }

    public function down()
    {
        $this->execute('DELETE FROM static_content_data WHERE static_content_id IN (71, 72);');
        $this->execute('DELETE FROM static_content WHERE id  IN (71, 72);');
    }
}
