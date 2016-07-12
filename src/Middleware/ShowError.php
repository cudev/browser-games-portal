<?php

namespace Ludos\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Zend\Stratigility\ErrorMiddlewareInterface;

class ShowError implements ErrorMiddlewareInterface
{
    private $templateAggregatorMiddleware;

    public function __construct(TemplateAggregatorMiddleware $templateAggregatorMiddleware)
    {
        $this->templateAggregatorMiddleware = $templateAggregatorMiddleware;
    }

    /**
     * {@inheritdoc}
     * @throws \InvalidArgumentException
     */
    public function __invoke($error, Request $request, Response $response, callable $out = null)
    {
        error_log($error);
        return $out($request, $response, $error);
    }
}
