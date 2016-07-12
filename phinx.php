<?php

/** @var \Interop\Container\ContainerInterface $container */
$container = require __DIR__ . '/config/container.php';

/** @var \Doctrine\ORM\EntityManager $entityManager */
$entityManager = $container->get(\Doctrine\ORM\EntityManager::class);
/** @var PDO $connection */
$connection = $entityManager->getConnection()->getWrappedConnection();

return [
    'environments' => [
        'default_database' => 'development',
        'default_migration_table' => 'migrations',
        'development' => [
            'name' => $container->get('config')['doctrine']['connection']['dbname'],
            'connection' => $connection
        ],
        'production' => [
            'name' => $container->get('config')['doctrine']['connection']['dbname'],
            'connection' => $connection
        ]
    ],
    'paths' => [
        'migrations' => realpath(__DIR__ . '/db/migrations'),
        'seeds' => realpath(__DIR__ . '/db/seeds')
    ]
];