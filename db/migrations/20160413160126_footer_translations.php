<?php

use Phinx\Migration\AbstractMigration;

class FooterTranslations extends AbstractMigration
{
    public function up()
    {
        $this->insert('static_content', [
            ['id' => 62, 'access_key' => 'footer.contact.description'],
            ['id' => 63, 'access_key' => 'terms-of-use'],
            ['id' => 64, 'access_key' => 'privacy-policy'],
            ['id' => 65, 'access_key' => 'about-us']
        ]);
        $this->insert('static_content_data', [
            [
                'translation' => 'Feel free to ask any questions',
                'static_content_id' => 62,
                'locale_id' => 1
            ],
            [
                'translation' => 'Terms of use',
                'static_content_id' => 63,
                'locale_id' => 1
            ],
            [
                'translation' => 'Privacy Policy',
                'static_content_id' => 64,
                'locale_id' => 1
            ],
            [
                'translation' => 'About us',
                'static_content_id' => 65,
                'locale_id' => 1
            ]
        ]);
    }

    public function down()
    {
        $this->execute('DELETE FROM static_content_data WHERE static_content_id IN (62, 63, 64, 65);');
        $this->execute('DELETE FROM static_content WHERE id IN (62, 63, 64, 65);');
    }
}
