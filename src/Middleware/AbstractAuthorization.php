<?php

namespace Ludos\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractAuthorization extends Authentication
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $user = $this->recognizeUser();
        if ($this->accessDenied($user)) {
            return $next($request, $response->withStatus(404), 'Not found');
        }
        return $next($request->withAttribute('user', $user), $response);
    }

    abstract protected function accessDenied($user);
}
