<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Cudev Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
