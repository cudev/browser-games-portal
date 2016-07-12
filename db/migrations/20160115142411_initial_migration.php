<?php

use Ludos\Entity\Role;
use Ludos\Entity\User;
use Phinx\Migration\AbstractMigration;

class InitialMigration extends AbstractMigration
{
    public function up()
    {
        $localesTable = $this->table('locales')
            ->addColumn('domain', 'string')
            ->addColumn('language', 'string')
            ->addColumn('country', 'string', ['null' => true])
            ->addIndex(['domain', 'language'], ['unique' => true]);
        $localesTable->create();

        $staticContentTable = $this->table('static_content')
            ->addColumn('access_key', 'string')
            ->addColumn('page_name', 'string', ['null' => true])
            ->addIndex(['access_key'], ['unique' => true])
            ->addTimestamps();
        $staticContentTable->create();

        $this->table('static_content_data')
            ->addColumn('translation', 'string')
            ->addColumn('static_content_id', 'integer')
            ->addColumn('locale_id', 'integer')
            ->addForeignKey('static_content_id', $staticContentTable, 'id')
            ->addForeignKey('locale_id', $localesTable, 'id')
            ->addTimestamps()
            ->create();

        $providersTable = $this->table('providers')
            ->addColumn('name', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('uri', 'string', ['limit' => 150, 'null' => true])
            ->addTimestamps();
        $providersTable->create();

        $gamesTable = $this->table('games')
            ->addColumn('name', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('type', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('slug', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('url', 'string', ['limit' => 300, 'null' => false])
            ->addColumn('enabled', 'boolean')
            ->addColumn('thumbnail', 'string', ['limit' => 300, 'null' => true])
            ->addColumn('height', 'integer', ['null' => true])
            ->addColumn('width', 'integer', ['null' => true])
            ->addColumn('plays', 'integer', ['default' => 0])
            ->addIndex(['slug'], ['unique' => true])
            ->addTimestamps();
        $gamesTable->create();

        $this->table('games_meta', ['id' => false, 'primary_key' => ['id']])
            ->addColumn('id', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('game_id', 'integer')
            ->addColumn('provider_id', 'integer')
            ->addColumn('data', 'blob')
            ->addForeignKey('provider_id', $providersTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('game_id', $gamesTable, 'id', ['delete' => 'CASCADE'])
            ->addTimestamps()
            ->create();

        $tagsTable = $this->table('tags')
            ->addColumn('enabled', 'boolean')
            ->addColumn('featured', 'boolean')
            ->addTimestamps();
        $tagsTable->create();

        $bannersTable = $this->table('banners')
            ->addColumn('enabled', 'boolean')
            ->addColumn('game_id', 'integer')
            ->addColumn('picture', 'string', ['null' => true])
            ->addColumn('priority', 'integer')
            ->addForeignKey('game_id', $gamesTable, 'id', ['delete' => 'CASCADE'])
            ->addTimestamps();
        $bannersTable->create();

        $bannerTitlesTable = $this->table('banner_titles')
            ->addColumn('translation', 'text', ['null' => true])
            ->addColumn('banner_id', 'integer')
            ->addColumn('locale_id', 'integer', ['null' => false, 'default' => 0])
            ->addForeignKey('banner_id', $bannersTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('locale_id', $localesTable, 'id', ['delete' => 'CASCADE'])
            ->addTimestamps();
        $bannerTitlesTable->create();

        $rolesTable = $this->table('roles')
            ->addColumn('name', 'string', ['null' => false]);
        $rolesTable->create();

        /**
         * "password_hash" length supposed to be 255 symbols because we use @const PASSWORD_DEFAULT when hashing
         * @see http://www.php.net/manual/en/password.constants.php
         */
        $usersTable = $this->table('users')
            ->addColumn('name', 'string', ['limit' => 50, 'null' => false])
            ->addColumn('email', 'string', ['limit' => 100, 'null' => false])
            ->addColumn('password_hash', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('picture', 'string', ['null' => true])
            ->addColumn('birthday', 'date', ['null' => true])
            ->addColumn('gender', 'string', ['null' => true])
            ->addColumn('subscribed', 'boolean', ['default' => false])
            ->addColumn('role_id', 'integer', ['null' => false])
            ->addForeignKey('role_id', $rolesTable, 'id', ['delete' => 'CASCADE'])
            ->addIndex(['email'], ['unique' => true])
            ->addIndex(['name'], ['unique' => true])
            ->addTimestamps();
        $usersTable->create();

        $providerCategoriesTable = $this->table('provider_categories')
            ->addColumn('name', 'string', ['limit' => 150, 'null' => false])
            ->addColumn('provider_id', 'integer')
            ->addForeignKey('provider_id', $providersTable, 'id', ['delete' => 'CASCADE'])
            ->addIndex(['name', 'provider_id'], ['unique' => true])
            ->addTimestamps();
        $providerCategoriesTable->create();

        $tagsNamesTable = $this->table('tag_names')
            ->addColumn('translation', 'text', ['null' => true])
            ->addColumn('slug', 'string', ['limit' => 150, 'null' => true])
            ->addColumn('tag_id', 'integer')
            ->addColumn('locale_id', 'integer', ['null' => false, 'default' => 0])
            ->addIndex(['slug'])
            ->addForeignKey('tag_id', $tagsTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('locale_id', $localesTable, 'id', ['delete' => 'CASCADE'])
            ->addTimestamps();
        $tagsNamesTable->create();

        $gameDescriptionsTable = $this->table('game_descriptions')
            ->addColumn('translation', 'text', ['null' => true])
            ->addColumn('game_id', 'integer')
            ->addColumn('locale_id', 'integer', ['null' => false, 'default' => 0])
            ->addForeignKey('game_id', $gamesTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('locale_id', $localesTable, 'id', ['delete' => 'CASCADE'])
            ->addTimestamps();
        $gameDescriptionsTable->create();

        $this->table('users_bookmarked_games')
            ->addColumn('user_id', 'integer')
            ->addColumn('game_id', 'integer')
            ->addForeignKey('user_id', $usersTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('game_id', $gamesTable, 'id', ['delete' => 'CASCADE'])
            ->addIndex(['user_id', 'game_id'], ['unique' => true])
            ->create();

        $this->table('users_played_games')
            ->addColumn('user_id', 'integer')
            ->addColumn('game_id', 'integer')
            ->addForeignKey('user_id', $usersTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('game_id', $gamesTable, 'id', ['delete' => 'CASCADE'])
            ->addIndex(['user_id', 'game_id'], ['unique' => true])
            ->create();

        $this->table('comments')
            ->addColumn('user_id', 'integer')
            ->addColumn('game_id', 'integer')
            ->addColumn('locale_id', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('body', 'text', ['null' => false])
            ->addForeignKey('user_id', $usersTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('game_id', $gamesTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('locale_id', $localesTable, 'id', ['delete' => 'CASCADE'])
            ->addIndex(['user_id', 'game_id', 'locale_id'])
            ->addTimestamps()
            ->create();

        $this->table('game_ratings')
            ->addColumn('user_id', 'integer')
            ->addColumn('game_id', 'integer')
            ->addColumn('rating', 'integer', ['null' => false])
            ->addForeignKey('user_id', $usersTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('game_id', $gamesTable, 'id', ['delete' => 'CASCADE'])
            ->addIndex(['user_id', 'game_id'], ['unique' => true])
            ->addTimestamps()
            ->create();

        $this->table('games_tags')
            ->addColumn('game_id', 'integer')
            ->addColumn('tag_id', 'integer')
            ->addForeignKey('game_id', $gamesTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('tag_id', $tagsTable, 'id', ['delete' => 'CASCADE'])
            ->addIndex(['game_id', 'tag_id'], ['unique' => true])
            ->create();

        $this->table('tags_provider_categories')
            ->addColumn('tag_id', 'integer')
            ->addColumn('provider_category_id', 'integer')
            ->addForeignKey('tag_id', $tagsTable, 'id', ['delete' => 'CASCADE'])
            ->addForeignKey('provider_category_id', $providerCategoriesTable, 'id', ['delete' => 'CASCADE'])
            ->addIndex(['tag_id', 'provider_category_id'], ['unique' => true])
            ->create();

        $this->insertData();
    }

    public function down()
    {
        $this->table('tags_provider_categories')->drop();
        $this->table('games_tags')->drop();
        $this->table('game_ratings')->drop();
        $this->table('comments')->drop();
        $this->table('users_played_games')->drop();
        $this->table('users_bookmarked_games')->drop();
        $this->table('game_descriptions')->drop();
        $this->table('tag_names')->drop();
        $this->table('provider_categories')->drop();
        $this->table('users')->drop();
        $this->table('roles')->drop();
        $this->table('banner_titles')->drop();
        $this->table('banners')->drop();
        $this->table('tags')->drop();
        $this->table('games_meta')->drop();
        $this->table('games')->drop();
        $this->table('providers')->drop();
        $this->table('static_content_data')->drop();
        $this->table('static_content')->drop();
        $this->table('locales')->drop();
    }

    private function insertData()
    {
        $providers = [
            [
                'name' => 'Spilgames'
            ]
        ];

        $locales = [
            [
                'id' => 0,
                'language' => 'en',
                'domain' => 'games.com.loc'
            ]
        ];

        $roles = [
            [
                'name' => Role::ADMIN,
            ],
            [
                'name' => Role::USER,
            ]
        ];

        $users = [
            [
                'name' => 'Administrator',
                'email' => 'admin@playgames.cool',
                'password_hash' => User::makePasswordHash('cbfgrt35'),
                'role_id' => 1
            ]
        ];

        $staticContent = [
            ['access_key' => 'sign.in'],
            ['access_key' => 'sign.up'],
            ['access_key' => 'sign.out'],
            ['access_key' => 'categories'],
            ['access_key' => 'email'],
            ['access_key' => 'password'],
            ['access_key' => 'password.repeat'],
            ['access_key' => 'password.wrong'],
            ['access_key' => 'email.wrong'],
            ['access_key' => 'email.taken'],
            ['access_key' => 'password.wrong.length'],
            ['access_key' => 'password.wrong.spaces'],
            ['access_key' => 'password.wrong.match'],
            ['access_key' => 'games.most.rated'],
            ['access_key' => 'games.most.recent'],
            ['access_key' => 'games.most.played'],
            ['access_key' => 'games.most.discussed'],
            ['access_key' => 'games.last.played'],
            ['access_key' => 'games.all'],
            ['access_key' => 'games.similar'],
            ['access_key' => 'view.more'],
            ['access_key' => 'games.last.played.placeholder'],
            ['access_key' => 'game.featured'],
            ['access_key' => 'game.featured.tag'],
            ['access_key' => 'game.times.played'],
            ['access_key' => 'footer.social.title'],
            ['access_key' => 'footer.social.description'],
            ['access_key' => 'footer.newsletter.title'],
            ['access_key' => 'footer.newsletter.description'],
            ['access_key' => 'footer.newsletter.subscribe'],
            ['access_key' => 'footer.newsletter.subscribe.placeholder'],
            ['access_key' => 'footer.contact.title'],
            ['access_key' => 'comment.create'],
            ['access_key' => 'comment.placeholder'],
            ['access_key' => 'comment.unauthorized'],
            ['access_key' => 'games.recommended'],
            ['access_key' => 'account.name'],
            ['access_key' => 'account.email'],
            ['access_key' => 'account.birthday'],
            ['access_key' => 'account.gender'],
            ['access_key' => 'account.gender.male'],
            ['access_key' => 'account.gender.female'],
            ['access_key' => 'account.picture.upload'],
            ['access_key' => 'account.picture.remove'],
            ['access_key' => 'account.save'],
            ['access_key' => 'account.edit'],
            ['access_key' => 'error.header1'],
            ['access_key' => 'error.header2'],
            ['access_key' => 'error.description'],
            ['access_key' => 'error.explanation'],
        ];


        $staticContentData = [
            [
                'translation' => 'Sign in',
                'static_content_id' => 1,
                'locale_id' => 1
            ],
            [
                'translation' => 'Sign up',
                'static_content_id' => 2,
                'locale_id' => 1
            ],
            [
                'translation' => 'Sign out',
                'static_content_id' => 3,
                'locale_id' => 1
            ],
            [
                'translation' => 'Categories',
                'static_content_id' => 4,
                'locale_id' => 1
            ],
            [
                'translation' => 'Email',
                'static_content_id' => 5,
                'locale_id' => 1
            ],
            [
                'translation' => 'Password',
                'static_content_id' => 6,
                'locale_id' => 1
            ],
            [
                'translation' => 'Password again',
                'static_content_id' => 7,
                'locale_id' => 1
            ],
            [
                'translation' => 'Wrong password.',
                'static_content_id' => 8,
                'locale_id' => 1
            ],
            [
                'translation' => 'Incorrect email.',
                'static_content_id' => 9,
                'locale_id' => 1
            ],
            [
                'translation' => 'Already in use.',
                'static_content_id' => 10,
                'locale_id' => 1
            ],
            [
                'translation' => 'Length must be from 6 to 50 characters.',
                'static_content_id' => 11,
                'locale_id' => 1
            ],
            [
                'translation' => 'Whitespaces are not allowed.',
                'static_content_id' => 12,
                'locale_id' => 1
            ],
            [
                'translation' => 'Password doesn\'t match.',
                'static_content_id' => 13,
                'locale_id' => 1
            ],
            [
                'translation' => 'Most rated',
                'static_content_id' => 14,
                'locale_id' => 1
            ],
            [
                'translation' => 'Most recent',
                'static_content_id' => 15,
                'locale_id' => 1
            ],
            [
                'translation' => 'Most played',
                'static_content_id' => 16,
                'locale_id' => 1
            ],
            [
                'translation' => 'Most discussed',
                'static_content_id' => 17,
                'locale_id' => 1
            ],
            [
                'translation' => 'Last played',
                'static_content_id' => 18,
                'locale_id' => 1
            ],
            [
                'translation' => 'All games',
                'static_content_id' => 19,
                'locale_id' => 1
            ],
            [
                'translation' => 'Similar games',
                'static_content_id' => 20,
                'locale_id' => 1
            ],
            [
                'translation' => 'view more',
                'static_content_id' => 21,
                'locale_id' => 1
            ],
            [
                'translation' => 'Your game will be here',
                'static_content_id' => 22,
                'locale_id' => 1
            ],
            [
                'translation' => 'Featured game',
                'static_content_id' => 23,
                'locale_id' => 1
            ],
            [
                'translation' => 'Featured game of {tag}',
                'static_content_id' => 24,
                'locale_id' => 1
            ],
            [
                'translation' => '{plays, plural,
                    =0 {Not yet played}
                    =1 {Played one time}
                    other {Played # times}}',
                'static_content_id' => 25,
                'locale_id' => 1
            ],
            [
                'translation' => 'Be social',
                'static_content_id' => 26,
                'locale_id' => 1
            ],
            [
                'translation' => 'Find us on social networks',
                'static_content_id' => 27,
                'locale_id' => 1
            ],
            [
                'translation' => 'Stay tuned',
                'static_content_id' => 28,
                'locale_id' => 1
            ],
            [
                'translation' => 'Be first to hear about our new games',
                'static_content_id' => 29,
                'locale_id' => 1
            ],
            [
                'translation' => 'Subscribe',
                'static_content_id' => 30,
                'locale_id' => 1
            ],
            [
                'translation' => 'Your email',
                'static_content_id' => 31,
                'locale_id' => 1
            ],
            [
                'translation' => 'Contact us',
                'static_content_id' => 32,
                'locale_id' => 1
            ],
            [
                'translation' => 'Leave a comment',
                'static_content_id' => 33,
                'locale_id' => 1
            ],
            [
                'translation' => 'Say something fun!',
                'static_content_id' => 34,
                'locale_id' => 1
            ],
            [
                'translation' => 'You must be logged in to leave a comment!',
                'static_content_id' => 35,
                'locale_id' => 1
            ],
            [
                'translation' => 'Recommended for you',
                'static_content_id' => 36,
                'locale_id' => 1
            ],
            [
                'translation' => 'Name',
                'static_content_id' => 37,
                'locale_id' => 1
            ],
            [
                'translation' => 'Email',
                'static_content_id' => 38,
                'locale_id' => 1
            ],
            [
                'translation' => 'Birthday',
                'static_content_id' => 39,
                'locale_id' => 1
            ],
            [
                'translation' => 'Gender',
                'static_content_id' => 40,
                'locale_id' => 1
            ],
            [
                'translation' => 'Male',
                'static_content_id' => 41,
                'locale_id' => 1
            ],
            [
                'translation' => 'Female',
                'static_content_id' => 42,
                'locale_id' => 1
            ],
            [
                'translation' => 'Upload new picture',
                'static_content_id' => 43,
                'locale_id' => 1
            ],
            [
                'translation' => 'Remove',
                'static_content_id' => 44,
                'locale_id' => 1
            ],
            [
                'translation' => 'Edit profile',
                'static_content_id' => 45,
                'locale_id' => 1
            ],
            [
                'translation' => 'Save profile',
                'static_content_id' => 46,
                'locale_id' => 1
            ],
            [
                'translation' => 'Oops!',
                'static_content_id' => 47,
                'locale_id' => 1
            ],
            [
                'translation' => 'This is awkward.',
                'static_content_id' => 48,
                'locale_id' => 1
            ],
            [
                'translation' => 'We encountered a {code} {reason} error.',
                'static_content_id' => 49,
                'locale_id' => 1
            ],
            [
                'translation' => 'You are looking for something that doesn\'t exist or may have moved. Check out one of the links on this page or head back to {url}.',
                'static_content_id' => 50,
                'locale_id' => 1
            ]
        ];

        $this->table('providers')->insert($providers)->update();
        $this->table('locales')->insert($locales)->update();
        $this->table('roles')->insert($roles)->update();
        $this->table('users')->insert($users)->update();
        $this->table('static_content')->insert($staticContent)->update();
        $this->table('static_content_data')->insert($staticContentData)->update();
    }
}
