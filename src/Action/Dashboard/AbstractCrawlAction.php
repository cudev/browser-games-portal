<?php

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
