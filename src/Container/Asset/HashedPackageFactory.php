<?php

namespace Ludos\Container\Asset;

use Interop\Container\ContainerInterface;
use Ludos\Asset\HashedPackage;
use Symfony\Component\Asset\VersionStrategy\EmptyVersionStrategy;
use UnexpectedValueException;

class HashedPackageFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        /** @noinspection UnSafeIsSetOverArrayInspection */
        if (!isset($config['assets']['hashed'])) {
            throw new UnexpectedValueException('Cannot find path in config for hashed assets package');
        }
        return new HashedPackage($config['assets']['hashed'], new EmptyVersionStrategy());
    }
}
