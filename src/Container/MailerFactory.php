<?php

namespace Ludos\Container;

use Interop\Container\ContainerInterface;
use Cudev\OrdinaryMail\Mailer;

class MailerFactory
{
    public function __invoke(ContainerInterface $container): Mailer
    {
        $config = $container->get('config')['amazon-ses'];
        $mailer = new Mailer();
        $mailer->setHost($config['host'])
            ->setPort($config['port'])
            ->setUsername($config['username'])
            ->setPassword($config['password']);
        return $mailer;
    }
}
