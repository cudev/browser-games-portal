<?php

namespace Ludos\Container;

use Aura\Session\SessionFactory as AuraSessionFactory;

class SessionFactory
{
    public function __invoke()
    {
        return (new AuraSessionFactory())->newInstance($_COOKIE);
    }
}
