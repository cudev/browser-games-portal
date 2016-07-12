<?php

namespace Ludos\Action;

use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Game;
use Ludos\Entity\PlayActivityEntry;
use Ludos\Entity\Repositories\GameRepository;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class ShowGame
{
    protected $entityManager;
    protected $templateRenderer;
    protected $serializer;

    public function __construct(
        TemplateRendererInterface $templateRenderer,
        EntityManager $entityManager,
        Serializer $serializer
    ) {
        $this->templateRenderer = $templateRenderer;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $gameSlug = $request->getAttribute('gameSlug');
        $game = null;
        if ($gameSlug !== null) {
            /** @var Game $game */
            $game = $this->entityManager->getRepository(Game::class)->findOneBy(['slug' => $gameSlug]);
        }
        if ($game === null) {
            return $next($request, $response, 404);
        }

        $game->incrementPlays();
        $this->entityManager->persist($game);

        /** @var User|null $user */
        $user = $request->getAttribute('user');
        if ($user !== null) {
            /** @var PlayActivityEntry $playActivityEntry */
            $playActivityEntry = $this->entityManager
                ->getRepository(PlayActivityEntry::class)
                ->findOneBy(['user' => $user->getId(), 'game' => $game->getId()]);
            if ($playActivityEntry === null) {
                $playActivityEntry = new PlayActivityEntry($user, $game);
            } else {
                $playActivityEntry->updateTimestamps();
            }
            $this->entityManager->persist($playActivityEntry);
        } else {
            $playedGamesIds = json_decode($request->getCookieParams()['playedGames'] ?? [], true);
            if (!in_array($game->getId(), $playedGamesIds, true)) {
                $playedGamesIds[] = $game->getId();
            }
            setcookie('playedGames', json_encode($playedGamesIds), null, '/');
        }

        $this->entityManager->flush();

        /** @var GameRepository $gameRepository */
        $gameRepository = $this->entityManager->getRepository(Game::class);

        $topGames = [];
        $gameTags = $game->getTags();
        if (count($gameTags) !== 0) {
            $topGames = $gameRepository->paginateActiveByTag(
                $game->getTags()->first(),
                $request->getAttribute(DetectSupportedGames::class, null),
                GameRepository::SORT_TOP,
                1,
                6
            );
        }

        $params = [
            'serializedUser' => $this->serializer->serialize($user, 'json'),
            'game' => $game,
            'user' => $user,
            'topGames' => $topGames,
            'supportedGameTypes' => $request->getAttribute(DetectSupportedGames::class, []),
            'canonical' => $request->getUri()
        ];
        return new HtmlResponse($this->templateRenderer->render('app::game', $params));
    }
}
