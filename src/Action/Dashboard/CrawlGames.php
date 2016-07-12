<?php

namespace Ludos\Action\Dashboard;

use LimitIterator;
use Ludos\Entity\Game\Description;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Game\Meta;
use Ludos\Entity\Game\Tag;
use Ludos\Entity\Locale;
use Ludos\Entity\Provider\Category;
use Ludos\Entity\Provider\Provider;
use Ludos\Entity\Repositories\GameRepository;
use Ludos\Schedule\TaskFailedException;
use Ludos\Schedule\TaskInterface;

class CrawlGames extends AbstractCrawlAction
{
    protected function save(\Iterator $limitIterator)
    {
        $usedSlugs = [];
        /** @var GameRepository $gameRepository */
        $gameRepository = $this->entityManager->getRepository(Game::class);
        $providerRepository = $this->entityManager->getRepository(Provider::class);
        $localeRepository = $this->entityManager->getRepository(Locale::class);
        $categoryRepostory = $this->entityManager->getRepository(Category::class);
        $tagRepository = $this->entityManager->getRepository(Tag::class);
        /** @var Game $game */
        foreach ($limitIterator as $game) {
            if (array_key_exists($game->getSlug(), $usedSlugs)
                || null !== $gameRepository->findOneBySlug($game->getSlug())
            ) {
                $game->setSlug(null);
            } else {
                // Keep track of slugs that aren't in the database yet
                $usedSlugs[$game->getSlug()] = true;
            }

            $game->setDescriptions(
                $game->getDescriptions()->map(function (Description $description) use ($localeRepository) {
                    $locale = $localeRepository->find($description->getLocale()->getId());
                    $description->setLocale($locale);
                    return $description;
                })
            );

            foreach ($game->getCategories() as $category) {
                /** @var Category $storedCategory */
                $storedCategory = $categoryRepostory->findOneBy(['name' => $category->getName()]);
                if ($storedCategory !== null) {
                    foreach ($storedCategory->getTags() as $tag) {
                        $game->addTag($tag);
                    }
                }
            }

            foreach ($game->getMeta() as $meta) {
                $provider = $providerRepository->find($meta->getProvider()->getId());
                $meta->setProvider($provider);
                $this->entityManager->persist($meta);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param Game $game
     */
    protected function isEntityExists($game): bool
    {
        $meta = $game->getMeta()[0];
        return null !== $this->entityManager->getRepository(Meta::class)->find($meta->getId());
    }

    protected function normalise(LimitIterator $limitIterator)
    {
        $data = [];
        foreach ($limitIterator as $key => $item) {
            $normalized = $this->serializer->normalize($item);
            $normalized += [
                'index' => $limitIterator->getPosition() + 1,
                'cacheKey' => $key,
                'crawlStatus' => $this->isEntityExists($item) ? 'Exists' : 'New'
            ];
            $data[] = $normalized;
        }
        return $data;
    }
}
