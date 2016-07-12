<?php

namespace Ludos\Container;

use Interop\Container\ContainerInterface;
use Ludos\Middleware\TemplateAggregatorMiddleware;

class TemplateAggregatorMiddlewareFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $aggregators = [];

        foreach ($config['templates']['aggregators'] as $aggregatorClassName) {
            $aggregators[] = $container->get($aggregatorClassName);
        }

        return new TemplateAggregatorMiddleware($aggregators);
    }
}
