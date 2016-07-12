<?php

namespace Ludos\Template;

use Zend\Expressive\Exception\InvalidArgumentException;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\Twig\TwigRenderer;

class Renderer implements TemplateRendererInterface
{
    private $twigRenderer;
    private $aggregators;

    /**
     * @param TwigRenderer $twigRenderer
     * @param AggregatorInterface[]|null $aggregators
     */
    public function __construct(TwigRenderer $twigRenderer, array $aggregators = null)
    {
        $this->twigRenderer = $twigRenderer;
        $this->aggregators = $aggregators;
    }

    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     */
    public function render($name, $params = [])
    {
        if ($this->aggregators !== null) {
            foreach ($this->aggregators as $aggregator) {
                if ($aggregator->hasTemplateName($name)) {
                    $params = array_merge($params, $aggregator->getTemplateVariables());
                }
            }
        }
        return $this->twigRenderer->render($name, $params);
    }

    /**
     * {@inheritdoc}
     */
    public function addPath($path, $namespace = null)
    {
        $this->twigRenderer->addPath($path, $namespace);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths()
    {
        $this->twigRenderer->getPaths();
    }

    /**
     * {@inheritdoc}
     */
    public function addDefaultParam($templateName, $param, $value)
    {
        $this->twigRenderer->addDefaultParam($templateName, $param, $value);
    }
}
