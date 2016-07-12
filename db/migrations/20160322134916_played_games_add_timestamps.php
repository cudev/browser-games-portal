<?php

use Phinx\Migration\AbstractMigration;

class PlayedGamesAddTimestamps extends AbstractMigration
{
    public function up()
    {
        $this->table('users_played_games')
            ->rename('play_activity_entries')
            ->addTimestamps()
            ->update();
    }

    public function down()
    {
        $this->table('play_activity_entries')
            ->rename('users_played_games')
            ->removeColumn('created_at')
            ->removeColumn('updated_at')
            ->update();
    }
}
