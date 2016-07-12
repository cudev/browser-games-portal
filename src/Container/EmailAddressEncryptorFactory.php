<?php

namespace Ludos\Container;

use Cudev\OrdinaryMail\EmailAddressEncryptor;
use Interop\Container\ContainerInterface;

class EmailAddressEncryptorFactory
{
    public function __invoke(ContainerInterface $container): EmailAddressEncryptor
    {
        $config = $container->get('config');
        return new EmailAddressEncryptor($config['encryption']['key']);
    }
}
