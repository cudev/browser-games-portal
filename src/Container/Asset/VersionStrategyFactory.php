<?php

namespace Ludos\Container\Asset;

use Interop\Container\ContainerInterface;
use Ludos\Asset\VersionStrategies\TimestampVersionStrategy;

class VersionStrategyFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($config['assets']['path'])) {
            throw new UnexpectedValueException('Cannot find path in config for hashed assets package');
        }
        return new TimestampVersionStrategy($config['assets']['path']);
    }
}
