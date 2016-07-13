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

namespace Ludos\Template\Aggregators;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Game\Tag;
use Ludos\Entity\Game\TagName;
use Ludos\Entity\PlayActivityEntry;
use Ludos\Entity\Repositories\GameRepository;
use Ludos\Entity\User;
use Ludos\Template\AbstractAggregator;

class PlayedGamesAggregator extends AbstractAggregator
{
    private $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getTemplateVariables(): array
    {
        /** @var User|null $user */
        $user = $this->request->getAttribute('user');
        $tagSlug = $this->request->getAttribute('tagSlug', 'all');

        /** @var Tag|null $tag */
        $tag = null;
        if ($tagSlug !== null && $tagSlug !== 'all') {
            /** @var TagName|null $tagName */
            $tagName = $this->entityManager->getRepository(TagName::class)->findOneBy(['slug' => $tagSlug]);
            if ($tagName !== null) {
                $tag = $tagName->getTag();
            }
        }
        /** @var GameRepository $gameRepository */
        $gameRepository = $this->entityManager->getRepository(Game::class);

        $playedGames = new ArrayCollection();
        // TODO: use sessions
        // find last played games
        if ($user !== null) {
            $playActivityEntries = $this->entityManager->getRepository(PlayActivityEntry::class)->findByUser(
                $user,
                ['updatedAt' => 'DESC']
            );
            $playedGames = (new ArrayCollection($playActivityEntries))->map(function (PlayActivityEntry $entry) {
                return $entry->getGame();
            });
        } else {
            $playedGamesIds = $this->request->getCookieParams()['playedGames'] ?? null;
            if ($playedGamesIds !== null) {
                $criteria = [
                    'id' => json_decode($playedGamesIds, true),
                    'enabled' => true
                ];
                $playedGames = new ArrayCollection($gameRepository->findBy($criteria));
            }
        }

        // TODO: optimise
        if ($tag !== null) {
            $playedGames = $playedGames->filter(function (Game $game) use ($tag) {
                return $game->getTags()->contains($tag);
            });
        }

        $playedGames = $playedGames->slice(0, 6);
        $playedGames = array_pad($playedGames, 6, null);
        return ['playedGames' => $playedGames];
    }
}
