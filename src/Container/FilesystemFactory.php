<?php

namespace Ludos\Container;

use Interop\Container\ContainerInterface;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;

class FilesystemFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $localAdapter = new Local($container->get('config')['flysystem']['adapters']['local']['path']);
        return new Filesystem($localAdapter);
    }
}
