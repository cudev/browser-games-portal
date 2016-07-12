<?php

namespace Ludos\Container;

use Google_Client as GoogleClient;
use Interop\Container\ContainerInterface;

class GoogleClientFactory
{
    public function __invoke(ContainerInterface $container): GoogleClient
    {
        return new GoogleClient($container->get('config')['google']);
    }
}
