<?php

namespace Ludos\Middleware;

use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Authentication
{
    protected $entityManager;
    protected $session;

    public function __construct(EntityManager $entityManager, Session $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return $next($request->withAttribute('user', $this->recognizeUser()), $response);
    }

    /**
     * Extract current user from session
     * @return User|null
     */
    public function recognizeUser()
    {
        $userId = $this->session->getSegment(User::class)->get('userId');
        $user = null;
        if ($userId) {
            $user = $this->entityManager->getRepository(User::class)->find($userId);
        }
        return $user;
    }
}
