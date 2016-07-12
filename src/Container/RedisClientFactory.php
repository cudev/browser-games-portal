<?php

namespace Ludos\Container;

use Interop\Container\ContainerInterface;
use Predis\Client;

class RedisClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $client = new Client('tcp://' . $config['redis']['connection']['host'] .':6379');
        return $client;
    }
}
