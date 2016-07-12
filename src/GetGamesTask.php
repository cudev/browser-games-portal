<?php

namespace Ludos;

use Ludos\Crawling\Lock\RedisLock;
use GameFeed\Games;
use Ludos\Iterator\MapIterator;
use Predis\Client;
use Predis\Collection\Iterator\HashKey;

class GetGamesTask
{
    protected $lock;
    protected $games;
    protected $redis;

    const DATA_KEY = 'games:crawling-games#data';
    const LOCK_KEY = 'games:crawling-games#lock';

    public function __construct(Client $redis, Games $games)
    {
        $this->lock = new RedisLock($redis, static::LOCK_KEY);
        $this->games = $games;
        $this->redis = $redis;
    }

    public function __invoke()
    {
        if (!$this->lock->acquire()) {
            return;
        }

        foreach ($this->games as $key => $game) {
            $this->redis->hset(
                static::DATA_KEY,
                $key,
                serialize($game)
            );
        }

        $this->lock->release();
    }

    public function getCrawled()
    {
        $redisHashIterator = new HashKey($this->redis, static::DATA_KEY);
        $mapIterator = new MapIterator($redisHashIterator, function ($game) {
            return unserialize($game);
        });
        return $mapIterator;
    }

    public function countCrawled(): int
    {
        return count($this->redis->hkeys(static::DATA_KEY));
    }

    public function isCrawling()
    {
        return $this->lock->isLocked();
    }

    public function discardCrawled(array $keys = [])
    {
        if (count($keys) !== 0) {
            return (boolean)$this->redis->hdel(static::DATA_KEY, $keys);
        }
        return (boolean)$this->redis->del(static::DATA_KEY);
    }

    public function destroy()
    {
        $this->lock->release();
        $this->discardCrawled();
    }

    public function getCrawledClass(): string
    {
        return $this->crawler->getCrawledClass();
    }
}