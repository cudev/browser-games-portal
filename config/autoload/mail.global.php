<?php

use Ludos\Container\MailerFactory;
use Cudev\OrdinaryMail\Mailer;

return [
    'dependencies' => [
        'factories' => [
            Mailer::class => MailerFactory::class
        ]
    ],
    'amazon-ses' => [
        'host'      => getenv('EMAIL_SES_HOST'),
        'port'      => getenv('EMAIL_SES_PORT'),
        'username'  => getenv('EMAIL_SES_USER'),
        'password'  => getenv('EMAIL_SES_PASSWORD'),
        'name'      => getenv('EMAIL_SES_NAME'),
        'address'   => getenv('EMAIL_SES_ADDRESS')
    ]
];
