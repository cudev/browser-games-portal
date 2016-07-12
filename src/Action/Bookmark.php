<?php

namespace Ludos\Action;

use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Game;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Bookmark
{
    private $entityManager;
    private $session;

    public function __construct(EntityManager $entityManager, Session $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var User|null $user */
        $user = $request->getAttribute('user');

        $parsedRequestBody = json_decode($request->getBody(), true);

        $game = null;
        if (array_key_exists('gameId', $parsedRequestBody) && is_numeric($parsedRequestBody['gameId'])) {
            $game = $this->entityManager->getRepository(Game::class)->find($parsedRequestBody['gameId']);
        }

        if ($game === null) {
            return new JsonResponse(null, 400);
        }

        $user->toggleBookmarkedGame($game);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }
}
