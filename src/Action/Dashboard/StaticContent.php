<?php

namespace Ludos\Action\Dashboard;

use Doctrine\ORM\EntityManager;
use Ludos\Entity\Locale;
use Ludos\Entity\StaticContent as StaticContentEntity;
use Ludos\Entity\StaticContentData;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\JsonResponse;

class StaticContent extends AbstractResourceAction
{
    protected $entityManager;
    protected $cachePool;
    protected $serializer;

    public function __construct(
        EntityManager $entityManager,
        CacheItemPoolInterface $cachePool,
        Serializer $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->cachePool = $cachePool;
        $this->serializer = $serializer;
    }

    public function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        /** @var StaticContentEntity[] $staticContents */
        $staticContents = $this->entityManager->getRepository(StaticContentEntity::class)->findAll();
        /** @var Locale[] $locales */
        $locales = $this->entityManager->getRepository(Locale::class)->findAll();

        $serializedStaticContent = [];
        // TODO: find better solution to insert empty translations
        foreach ($staticContents as $staticContent) {
            foreach ($locales as $locale) {
                foreach ($staticContent->getStaticContentData() as $staticContentData) {
                    if ($staticContentData->getLocale()->getId() === $locale->getId()) {
                        continue 2;
                    }
                }
                $emptyStaticContentData = new StaticContentData();
                $emptyStaticContentData->setLocale($locale)
                    ->setTranslation('');
                $staticContent->addStaticContentData($emptyStaticContentData);
            }
            $serializedStaticContent[] = $this->serializer->normalize($staticContent);
        }

        return new JsonResponse(['data' => $serializedStaticContent]);
    }

    public function put(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $serializedStaticContents = json_decode($request->getBody()->getContents(), true);

        foreach ($serializedStaticContents as $serializedStaticContent) {
            $staticContent = $this->serializer->denormalize($serializedStaticContent, StaticContentEntity::class);
            $this->entityManager->persist($staticContent);
        }
        $this->entityManager->flush();

        // And clear translations cache
        $this->cachePool->deleteItem(StaticContentEntity::class);

        $normalizedStaticContent = [];
        foreach ($this->entityManager->getRepository(StaticContentEntity::class)->findAll() as $staticContent) {
            $normalizedStaticContent[] = $this->serializer->normalize($staticContent);
        }

        return new JsonResponse(['data' => $normalizedStaticContent]);
    }

    public function patch(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->post($request, $response, $next);
    }
}
