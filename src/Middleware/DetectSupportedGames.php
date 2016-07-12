<?php

namespace Ludos\Middleware;

use Detection\MobileDetect;
use Ludos\Entity\Game\Game;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DetectSupportedGames
{
    protected $mobileDetect;

    public function __construct(MobileDetect $mobileDetect)
    {
        $this->mobileDetect = $mobileDetect;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        if ($this->mobileDetect->isMobile($request->getHeaderLine('user-agent'), $request->getHeaders())) {
            $request = $request->withAttribute(static::class, Game::getMobileSupportedTypes());
        } else {
            $request = $request->withAttribute(static::class, Game::getDesktopSupportedTypes());
        }
        return $next($request, $response);
    }
}
