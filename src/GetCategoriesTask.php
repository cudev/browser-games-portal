<?php

namespace Ludos;

use Ludos\Entity\Game\Game;

class GetCategoriesTask extends GetGamesTask
{
    const DATA_KEY = 'games:crawling-categories#data';
    const LOCK_KEY = 'games:crawling-categories#lock';

    public function __invoke()
    {
        if (!$this->lock->acquire()) {
            return;
        }

        $categories = [];

        /** @var Game $game */
        foreach ($this->games as $key => $game) {
            foreach ($game->getCategories() as $category) {
                if (in_array($category->getName(), $categories, true)) {
                    continue;
                }
                $categories[] = $category->getName();
                $this->redis->hset(
                    self::DATA_KEY,
                    $key,
                    serialize($category)
                );
            }
        }

        $this->lock->release();
    }
}