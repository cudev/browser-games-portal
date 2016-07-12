<?php

namespace Ludos\Action\Dashboard;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Game\Tag;
use Ludos\Entity\Repositories\GameRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\JsonResponse;

class Games extends AbstractResourceAction
{
    protected $entityManager;
    protected $serializer;

    public function __construct(EntityManager $entityManager, Serializer $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function get(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $id = $request->getAttribute('id');
        /** @var GameRepository $gameRepository */
        $gameRepository = $this->entityManager->getRepository(Game::class);

        /** @var Game[]|ArrayCollection|null $games */
        if ($id) {
            $game = $gameRepository->find($id);
            if ($game === null) {
                return $next($request, $response->withStatus(404), 'Not found');
            }
            $games = [$game];
        } else {
            $queryParameters = $request->getQueryParams();
            $page = $queryParameters['page'] ?? 1;
            $limit = $queryParameters['limit'] ?? 10;
            $query = $queryParameters['query'] ?? null;
            $withoutDescription = array_key_exists('description', $queryParameters)
                ? $this->parseWithoutDescriptionsQuery($queryParameters['description'])
                : [];
            $games = $gameRepository->searchByName(
                $query,
                $withoutDescription,
                $limit,
                ($page - 1) * $limit
            );
            $totalGamesAmount = $gameRepository->countSearchByName($query, $withoutDescription);
        }
        $tags = $this->entityManager->getRepository(Tag::class)->findAll();

        $normalizedTags = [];
        foreach ($tags as $tag) {
            $normalizedTags[] = $this->serializer->normalize($tag);
        }

        $normalizedGames = [];
        foreach ($games as $game) {
            $normalizedGames[] = $this->serializer->normalize($game);
        }

        $responseData = [
            'data' => $id === null ? $normalizedGames : $normalizedGames[0],
            'included' => [
                'tags' => $normalizedTags
            ]
        ];

        if ($id === null) {
            $responseData['pages'] = ceil($totalGamesAmount / $limit);
        }

        return new JsonResponse($responseData);
    }

    private function parseWithoutDescriptionsQuery($query): array
    {
        $localeIds = [];
        if (strpos($query, ',') !== false) {
            $localeIds = explode(',', urldecode($query));
        } elseif (is_numeric($query)) {
            $localeIds = [$query];
        }
        return $localeIds;
    }


    public function delete(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $id = $request->getAttribute('id');

        if (!$id) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        /** @var Game $game */
        $game = $this->entityManager->getRepository(Game::class)->find($id);
        $this->entityManager->remove($game);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }

    public function post(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $serializedGame = $request->getBody()->getContents();

        /** @var Game $game */
        $game = $this->serializer->deserialize($serializedGame, Game::class, 'json');

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return $this->get($request, $response);
    }

    public function patch(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return $this->post($request, $response, $next);
    }
}
