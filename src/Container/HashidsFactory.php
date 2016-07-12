<?php

namespace Ludos\Container;

use Hashids\Hashids;
use Interop\Container\ContainerInterface;

class HashidsFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        return new Hashids($config['encryption']['key'], $config['hashids']['min_length']);
    }
}
