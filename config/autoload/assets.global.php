<?php

use Ludos\Container\Asset\UrlPackageFactory;
use Ludos\Container\Asset\VersionStrategyFactory;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;

return [
    'dependencies' => [
        'factories' => [
            VersionStrategyInterface::class => VersionStrategyFactory::class,
            UrlPackage::class => UrlPackageFactory::class
        ]
    ],
    'assets' => [
        'path' => __DIR__ . '/../../public',
        'hashed' => '/uploads'
    ]
];