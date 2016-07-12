<?php

use Cudev\OrdinaryMail\EmailAddressEncryptor;
use Hashids\Hashids;
use Ludos\Container\EmailAddressEncryptorFactory;
use Ludos\Container\HashidsFactory;

return [
    'dependencies' => [
        'factories' => [
            EmailAddressEncryptor::class => EmailAddressEncryptorFactory::class,
            Hashids::class => HashidsFactory::class
        ]
    ],
    'encryption' => [
        'key' => 'ThereWillComeSoftRains',
        'method' => 'AES-256-CBC'
    ],
    'encryption' => [
        'key' => getenv('ENCRYPTION_KEY'),
        'method' => 'AES-256-CBC'
    ],
    'hashids' => [
        'min_length' => 8
    ]
];