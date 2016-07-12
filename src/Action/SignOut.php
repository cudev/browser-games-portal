<?php

namespace Ludos\Action;

use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class SignOut
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
        return new JsonResponse(['success' => $this->session->destroy()]);
    }
}
