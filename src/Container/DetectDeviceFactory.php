<?php

namespace Ludos\Container;

use Detection\MobileDetect;
use Interop\Container\ContainerInterface;
use Ludos\Middleware\DetectSupportedGames;

class DetectDeviceFactory
{
    public function __invoke(ContainerInterface $container): DetectSupportedGames
    {
        return new DetectSupportedGames(new MobileDetect());
    }
}
