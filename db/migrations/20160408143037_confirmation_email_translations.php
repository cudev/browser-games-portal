<?php

use Phinx\Migration\AbstractMigration;

class ConfirmationEmailTranslations extends AbstractMigration
{
    public function up()
    {

        $this->insert('static_content', [
            ['id' => 55, 'access_key' => 'email.confirmation.subject'],
            ['id' => 56, 'access_key' => 'email.confirmation.explanation'],
            ['id' => 57, 'access_key' => 'email.confirmation.confirm'],
            ['id' => 58, 'access_key' => 'email.confirmation.greeting'],
            ['id' => 59, 'access_key' => 'email.browser'],
            ['id' => 60, 'access_key' => 'email.confirmation.message.confirmed'],
            ['id' => 61, 'access_key' => 'email.confirmation.message.cheer'],
        ]);
        $this->insert('static_content_data', [
            [
                'translation' => 'Welcome to {domain} - Please Verify Your Email',
                'static_content_id' => 55,
                'locale_id' => 1
            ],
            [
                'translation' => 'Hi {username},

 Thanks for registering your {domain} account!
To verify this email address, please click on the button below:',
                'static_content_id' => 56,
                'locale_id' => 1
            ],
            [
                'translation' => 'confirm registration',
                'static_content_id' => 57,
                'locale_id' => 1
            ],
            [
                'translation' => 'Our exciting world of online games is waiting for you, it’s time to play!',
                'static_content_id' => 58,
                'locale_id' => 1
            ],
            [
                'translation' => 'If you have any displaying issues, please click on the following link: {link}',
                'static_content_id' => 59,
                'locale_id' => 1
            ],
            [
                'translation' => 'Address confirmed',
                'static_content_id' => 60,
                'locale_id' => 1
            ],
            [
                'translation' => 'Go on, play them all! :)',
                'static_content_id' => 61,
                'locale_id' => 1
            ]
        ]);
    }

    public function down()
    {
        $this->execute('DELETE FROM static_content_data WHERE static_content_id IN (55, 56, 57, 58, 59, 60, 61);');
        $this->execute('DELETE FROM static_content WHERE id IN (55, 56, 57, 58, 59, 60, 61);');
    }
}
