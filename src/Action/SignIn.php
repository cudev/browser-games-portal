<?php

namespace Ludos\Action;

use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class SignIn
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
        $parsedRequestBody = json_decode($request->getBody()->getContents(), true);
        /** @var User $existingUser */
        $existingUser = $this
            ->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $parsedRequestBody['email']]);
        if ($existingUser !== null) {
            $success = $existingUser->passwordVerify($parsedRequestBody['password']);
            $errors = ['password' => ['wrong' => !$success]];
            $this->session->getSegment(User::class)->set('userId', $existingUser->getId());
            $this->session->commit();
        } else {
            $success = false;
            $errors = ['password' => ['wrong' => true]];
        }

        return new JsonResponse(['success' => $success, 'errors' => $errors]);
    }
}
