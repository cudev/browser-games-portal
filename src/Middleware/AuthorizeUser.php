<?php

namespace Ludos\Middleware;

class AuthorizeUser extends AbstractAuthorization
{
    protected $entityManager;
    protected $session;

    protected function accessDenied($user)
    {
        return $user === null;
    }
}
