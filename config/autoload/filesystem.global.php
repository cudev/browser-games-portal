<?php

return [
    'dependencies' => [
        'factories' => [
            \League\Flysystem\Filesystem::class => Ludos\Container\FilesystemFactory::class
        ]
    ],
    'flysystem' => [
        'adapters' => [
            'local' => [
                'path' => 'public/uploads'
            ]
        ]
    ]
];