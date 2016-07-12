<?php

namespace Ludos\Container;

use Interop\Container\ContainerInterface;
use Ludos\Template\Renderer;
use Zend\Expressive\Twig\TwigRenderer;

class RendererFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $aggregators = [];

        foreach ($config['templates']['aggregators'] as $aggregatorClassName) {
            $aggregators[] = $container->get($aggregatorClassName);
        }

        return new Renderer($container->get(TwigRenderer::class), $aggregators);
    }
}
