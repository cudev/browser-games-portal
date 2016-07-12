<?php

namespace Ludos\Container;

use Aura\Session\Session;
use Facebook\Facebook;
use Interop\Container\ContainerInterface;

class FacebookClientFactory
{
    public function __invoke(ContainerInterface $container): Facebook
    {
        /** @var Session $session */
        $session = $container->get(Session::class);

        // Session has to be started before Facebook initialisation.
        // Facebook detects session state and creates proper persistent storage,
        // that later used to validate CSRF token
        $session->start();

        return new Facebook($container->get('config')['facebook']);
    }
}
