<?php

namespace Ludos\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class CudevAccessOnly
{
    const COMPANY_IP = '82.117.232.9';
    const USER = 'cudevgames';
    const PASS = 'cudevgames';

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        if ($this->isAuthorized($request)) {
            return $next($request, $response);
        }
        return $response->withStatus(401)->withHeader('WWW-Authenticate', 'Basic realm="Protected Area"');
    }

    private function isAuthorized(ServerRequestInterface $request)
    {
        $serverParams = $request->getServerParams();
        $remoteAddress = $serverParams['REMOTE_ADDRESS'] ?? null;
        $user = $serverParams['PHP_AUTH_USER'] ?? null;
        $pass = $serverParams['PHP_AUTH_PW'] ?? null;
        return !($remoteAddress !== self::COMPANY_IP && ($user !== self::USER || $pass !== self::PASS));
    }
}
