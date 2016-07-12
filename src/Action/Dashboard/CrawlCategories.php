<?php

namespace Ludos\Action\Dashboard;

use LimitIterator;
use Ludos\Crawling\Crawler;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Provider\Category;
use Ludos\GetCategoriesTask;
use Ludos\Schedule\TaskFailedException;
use Ludos\Schedule\TaskInterface;

class CrawlCategories extends AbstractCrawlAction
{
    const TASK = GetCategoriesTask::class;

    protected function save(\Iterator $iterator)
    {
        /** @var Game $game */
        foreach ($iterator as $game) {
            foreach ($game->getCategories() as $crawledCategory) {
                if ($this->isEntityExists($crawledCategory)) {
                    continue;
                }
                $this->entityManager->persist($crawledCategory);
            }
        }
        $this->entityManager->flush();
    }

    /**
     * @param Category $category
     * @return bool
     */
    protected function isEntityExists($category): bool
    {
        return null !== $this->entityManager->getRepository(Category::class)->findOneByName($category->getName());
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
