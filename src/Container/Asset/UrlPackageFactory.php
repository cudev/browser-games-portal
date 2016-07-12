<?php

namespace Ludos\Container\Asset;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Asset\UrlPackage;
use Symfony\Component\Asset\VersionStrategy\VersionStrategyInterface;
use Zend\Diactoros\ServerRequestFactory;

class UrlPackageFactory
{
    public function __invoke(ContainerInterface $container): UrlPackage
    {
        return new UrlPackage(
            $this->extractBaseUrl(ServerRequestFactory::fromGlobals()),
            $container->get(VersionStrategyInterface::class)
        );
    }

    private function extractBaseUrl(ServerRequestInterface $request)
    {
        $uri = $request->getUri();
        $baseUrl = $uri->getScheme() . '://' . $uri->getHost();
        $port = $uri->getPort();
        if ($port !== null && $port !== 80) {
            $baseUrl .= ':' . $port;
        }
        return $baseUrl;
    }
}
