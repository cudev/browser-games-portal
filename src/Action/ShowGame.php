<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Cudev Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
