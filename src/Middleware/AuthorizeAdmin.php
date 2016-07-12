<?php

namespace Ludos\Middleware;

use Ludos\Entity\Role;
use Ludos\Entity\User;

class AuthorizeAdmin extends AbstractAuthorization
{
    protected $entityManager;
    protected $session;

    /**
     * @param User|null $user
     * @return bool
     */
    protected function accessDenied($user)
    {
        return $user === null || $user->getRole()->getName() !== Role::ADMIN;
    }
}
