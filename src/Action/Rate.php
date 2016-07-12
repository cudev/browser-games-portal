<?php

namespace Ludos\Action;

use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Game\Rating;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Rate
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
        /** @var User $user */
        $user = $request->getAttribute('user');

        $parsedRequestBody = json_decode($request->getBody(), true);

        /** @var Game|null $game */
        $game = null;
        if (array_key_exists('gameId', $parsedRequestBody) && is_numeric($parsedRequestBody['gameId'])) {
            $game = $this->entityManager->getRepository(Game::class)->find($parsedRequestBody['gameId']);
        }
        if ($game === null
            || (!array_key_exists('rating', $parsedRequestBody) || !is_numeric($parsedRequestBody['rating']))
        ) {
            return new JsonResponse(null, 400);
        }

        $rating = $this
            ->entityManager
            ->getRepository(Rating::class)
            ->findOneBy([
                'user' => $user->getId(),
                'game' => $game->getId()
            ]);

        if ($rating === null) {
            $rating = new Rating($user, $game);
        }

        $rating->setRating($parsedRequestBody['rating']);
        $this->entityManager->persist($rating);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }
}
