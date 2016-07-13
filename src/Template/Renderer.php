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
