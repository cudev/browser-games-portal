<?php

namespace Ludos\Container;

use Interop\Container\ContainerInterface;
use Stash\Driver\Redis;
use Stash\Pool;

class StashFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $host = $container->get('config')['redis']['connection']['host'];
        $redisDriver = new Redis(['servers' => [[$host]]]);
        return new Pool($redisDriver);
    }
}
