<?php

namespace Ludos\Crawling\Lock;

use Predis\Client;

class RedisLock implements LockInterface
{
    protected $redis;
    protected $id;

    public function __construct(Client $redis, $id)
    {
        $this->redis = $redis;
        $this->id = $id;
    }

    public function acquire()
    {
        if ($this->isLocked()) {
            return false;
        }
        return (boolean)$this->redis->set($this->id, 1);
    }

    public function release()
    {
        return (boolean)$this->redis->del($this->id);
    }

    public function isLocked()
    {
        return (boolean)$this->redis->exists($this->id);
    }
}
