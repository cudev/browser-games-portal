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

use CallbackFilterIterator;
use Doctrine\ORM\EntityManager;
use GameFeed\Games;
use GameFeed\Retrievers\Spilgames;
use Iterator;
use LimitIterator;
use Ludos\Crawling\CompositeClient;
use Ludos\Crawling\Crawler;
use Ludos\Entity\Locale;
use Ludos\Entity\Provider\Provider;
use Ludos\GetGamesTask;
use Ludos\Schedule\TaskQueue;
use Ludos\SpilgamesTransformer;
use Predis\Client;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\JsonResponse;

abstract class AbstractCrawlAction
{
    protected $entityManager;
    protected $taskQueue;
    protected $serializer;
    protected $providerMapping = [
        'Spilgames' => [
            'retriever' => Spilgames::class,
            'transformer' => SpilgamesTransformer::class
        ]
    ];

    private $cacheItemPool;
    private $redis;

    const ACTION_CRAWL = 'crawl';
    const ACTION_SAVE = 'save';
    const ACTION_DISCARD = 'discard';
    const TASK = GetGamesTask::class;

    public function __construct(
        EntityManager $entityManager,
        TaskQueue $taskQueue,
        Serializer $serializer,
        CacheItemPoolInterface $cacheItemPool,
        Client $redis
    ) {
        $this->entityManager = $entityManager;
        $this->taskQueue = $taskQueue;
        $this->serializer = $serializer;
        $this->cacheItemPool = $cacheItemPool;
        $this->redis = $redis;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var Provider $provider */
        $provider = $this->entityManager->getRepository(Provider::class)->find($request->getAttribute('providerId'));

        if ($provider === null) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        $page = $request->getQueryParams()['page'] ?? 1;
        $limit = $request->getQueryParams()['limit'] ?? 50;
        $action = $request->getQueryParams()['action'] ?? null;

        $keys = json_decode($request->getBody()->getContents(), true);

        $locale = $this->entityManager->getRepository(Locale::class)->getDefault();
        $spilgames = new Spilgames(new SpilgamesTransformer($provider, $locale), $this->cacheItemPool);

        $taskClassName = static::TASK;
        $task = new $taskClassName($this->redis, Games::from($spilgames));
        $crawledIterator = $task->getCrawled();

//        $task->destroy();


        if ($page && $limit) {
            $limitIterator = new LimitIterator($crawledIterator, ($page - 1) * $limit, $limit);
        } else {
            $limitIterator = new LimitIterator($crawledIterator);
        }

        switch ($action) {
            case self::ACTION_CRAWL:
                $this->taskQueue->enqueue($task);
                $delayed = true;
                break;
            case self::ACTION_SAVE:
                $keyFilteredIterator = new CallbackFilterIterator(
                    $limitIterator,
                    function ($current, $key) use ($keys) {
                        return in_array($key, $keys, true);
                    }
                );
                $this->save($keyFilteredIterator);
                break;
            case self::ACTION_DISCARD:
                $task->discardCrawled($keys);
                break;
        }


        $data = $this->normalise($limitIterator);

        return new JsonResponse([
            'isRunning' => $delayed ?? $task->isCrawling(),
            'data' => $data,
            'pages' => ceil($task->countCrawled() / $limit),
        ]);
        return new JsonResponse(false);
    }

    abstract protected function save(Iterator $limitIterator);

    abstract protected function isEntityExists($item): bool;

    abstract protected function normalise(LimitIterator $limitIterator);
}
