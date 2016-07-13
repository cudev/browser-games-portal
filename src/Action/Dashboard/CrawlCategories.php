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
