<?php

namespace Ludos\Middleware;

use Carbon\Carbon;
use Ludos\Entity\Locale;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class SetupLocale
{
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        Carbon::setLocale($request->getAttribute(Locale::class)->getLanguage());
        return $next($request, $response);
    }
}
