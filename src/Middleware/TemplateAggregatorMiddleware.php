<?php

namespace Ludos\Middleware;

use Ludos\Template\AbstractAggregator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class TemplateAggregatorMiddleware
{
    private $aggregators;

    /**
     * @param AbstractAggregator[]|null $aggregators
     */
    public function __construct(array $aggregators = null)
    {
        $this->aggregators = $aggregators;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        foreach ($this->aggregators as $aggregator) {
            $aggregator->setRequest($request);
        }
        return $next($request, $response);
    }
}
