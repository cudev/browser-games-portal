<?php

namespace Ludos\Middleware;

use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NotFound
{
    /**
     * {@inheritdoc}
     * @throws InvalidArgumentException
     */
    public function __invoke(Request $request, Response $response, callable $out = null)
    {
        return $out($request, $response->withStatus(404), 'Not found');
    }
}
