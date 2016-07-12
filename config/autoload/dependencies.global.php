<?php

use Aura\Intl\TranslatorLocator;
use Aura\Session\Session;
use Ludos\Asset\HashedPackage;
use Ludos\Container\ApplicationFactory;
use Ludos\Container\Asset\HashedPackageFactory;
use Ludos\Container\CompositeClientFactory;
use Ludos\Container\SerializerFactory;
use Ludos\Container\ServerRequestFactory;
use Ludos\Container\SessionFactory;
use Ludos\Container\StashFactory;
use Ludos\Container\TranslatorLocatorFactory;
use Ludos\Crawling\CompositeClient;
use Ludos\Schedule\TaskQueue;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Expressive\Application;
use Zend\Expressive\Helper\ServerUrlHelper;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Expressive\Helper\UrlHelperFactory;

return [
    // Provides application-wide services.
    // We recommend using fully-qualified class names whenever possible as
    // service names.
    'dependencies' => [
        // Use 'invokables' for constructor-less services, or services that do
        // not require arguments to the constructor. Map a service name to the
        // class name.
        'invokables' => [
            // Fully\Qualified\InterfaceName::class => Fully\Qualified\ClassName::class,
            ServerUrlHelper::class => ServerUrlHelper::class,
            TaskQueue::class => TaskQueue::class
        ],
        // Use 'factories' for services provided by callbacks/factory classes.
        'factories' => [
            Application::class => ApplicationFactory::class,
            ServerRequestInterface::class => ServerRequestFactory::class,
            UrlHelper::class => UrlHelperFactory::class,
            Session::class => SessionFactory::class,
            TranslatorLocator::class => TranslatorLocatorFactory::class,
            HashedPackage::class => HashedPackageFactory::class,
            CacheItemPoolInterface::class => StashFactory::class,
            CompositeClient::class => CompositeClientFactory::class,
            Serializer::class => SerializerFactory::class
        ],
    ],
];
