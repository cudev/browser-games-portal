<?php

return [
    'dependencies' => [
        'factories' => [
            Doctrine\ORM\EntityManager::class => Ludos\Container\EntityManagerFactory::class,
            Predis\Client::class => Ludos\Container\RedisClientFactory::class
        ]
    ],

    'doctrine' => [
        'connection' => [
            'driver' => 'pdo_mysql',
            'user' => getenv('DB_USER'),
            'password' => getenv('DB_PASSWORD'),
            'dbname' => getenv('DB_NAME'),
            'charset' => 'UTF8'
        ],
        'paths' => [
            'entities' => [
                'src/Entity'
            ],
            'proxy' => 'storage/cache/doctrine'
        ]
    ],
    'redis' => [
        'connection' => [
            'host' => getenv('REDIS_HOST')
        ]
    ]
];