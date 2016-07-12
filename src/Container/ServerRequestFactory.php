<?php

namespace Ludos\Container;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Ludos\Entity\Locale;
use Ludos\LocaleDetector;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\ServerRequestFactory as ZendRequestFactory;

class ServerRequestFactory
{
    public function __invoke(ContainerInterface $container): ServerRequestInterface
    {
        $request = ZendRequestFactory::fromGlobals();

        /** @var EntityManager $entityManager */
        $entityManager = $container->get(EntityManager::class);

        $localeDetector = new LocaleDetector($entityManager->getRepository(Locale::class), $request);
        $locale = $localeDetector->detect();
        return $request->withAttribute(Locale::class, $locale);
    }
}
