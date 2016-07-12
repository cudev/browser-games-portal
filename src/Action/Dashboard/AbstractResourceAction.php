<?php

namespace Ludos\Action\Dashboard;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractResourceAction
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $action = strtolower($request->getMethod());

        if (!method_exists($this, $action)) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        return $this->$action($request, $response, $next);
    }

    public function get(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return $next($request, $response->withStatus(405), $next);
    }


    public function delete(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return $next($request, $response->withStatus(405), $next);
    }

    public function post(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return $next($request, $response->withStatus(405), $next);
    }

    public function patch(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return $next($request, $response->withStatus(405), $next);
    }
}
