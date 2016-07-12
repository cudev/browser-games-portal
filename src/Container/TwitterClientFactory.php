<?php

namespace Ludos\Container;

use Abraham\TwitterOAuth\TwitterOAuth as TwitterClient;
use Interop\Container\ContainerInterface;

class TwitterClientFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config')['twitter'];
        return new TwitterClient($config['consumer_key'], $config['consumer_secret']);
    }
}
