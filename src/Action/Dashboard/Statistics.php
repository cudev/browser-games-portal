<?php

namespace Ludos\Action\Dashboard;

use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Repositories\GameRepository;
use Ludos\TrackResponseTime;
use Predis\Client;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Statistics
{
    private $entityManager;
    private $client;

    public function __construct(EntityManager $entityManager, Client $client)
    {
        $this->entityManager = $entityManager;
        $this->client = $client;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var GameRepository $gameRepository */
        $gameRepository = $this->entityManager->getRepository(Game::class);

        $statistics = [
            'allGames' => $gameRepository->countAll(),
            'enabledGames' => $gameRepository->countEnabled(),
            'withoutDescription' => $gameRepository->countWithoutDescription(),
            'averageResponseTime' => $this->client->get(TrackResponseTime::KEY_AVERAGE_TIME)
        ];
        return new JsonResponse($statistics);
    }
}
