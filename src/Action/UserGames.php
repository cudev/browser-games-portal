<?php

namespace Ludos\Action;

use Aura\Intl\TranslatorLocator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Locale;
use Ludos\Entity\StaticContent;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class UserGames
{
    private $entityManager;
    private $translatorLocator;

    public function __construct(EntityManager $entityManager, TranslatorLocator $translatorLocator)
    {
        $this->entityManager = $entityManager;
        $this->translatorLocator = $translatorLocator;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        $queryParams = $request->getQueryParams();
        if ($queryParams['filter']) {
            $filters = explode(',', $queryParams['filter']);
            if (in_array('bookmarked', $filters, true)) {
                /** @var ArrayCollection $bookmarkedGames */
                $games = $user->getBookmarkedGames();
            }
            if (in_array('commented', $filters, true)) {
                // TODO: implement
            }
            if (in_array('played', $filters, true)) {
                $games = $request->getAttribute('playedGames', []);
            }
        }

        /** @var Locale $locale */
        $locale = $request->getAttribute(Locale::class);
        $translator = $this->translatorLocator->get(StaticContent::class, $locale->getLanguage());

        $serialized = [];

        /** @var Game $game */
        foreach ($games as $game) {
            if ($game === null) {
                continue;
            }
            $serialized[] = [
                'created' => $game->getCreatedAt()->diffForHumans(),
                'name' => $game->getName(),
                'slug' => $game->getSlug(),
                'description' => $game->getDescription($locale->getLanguage()),
                'thumbnail' => $game->getThumbnail(),
                'rating' => $game->getAverageRating(),
                'plays' => $translator->translate('game.times.played', ['plays' => $game->getPlays()])
            ];
        }

        return new JsonResponse(['success' => true, 'data' => $serialized]);
    }
}
