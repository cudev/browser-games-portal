<?php

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
