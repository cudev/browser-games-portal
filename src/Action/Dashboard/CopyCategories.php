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

use Cocur\Slugify\Slugify;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Tag;
use Ludos\Entity\Game\TagName;
use Ludos\Entity\Locale;
use Ludos\Entity\Provider\Provider;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\JsonResponse;

class CopyCategories
{
    protected $entityManager;
    protected $serializer;

    public function __construct(EntityManager $entityManager, Serializer $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var Provider $provider */
        $provider = $this->entityManager->getRepository(Provider::class)->find($request->getAttribute('providerId'));

        if ($provider === null) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        $slugify = new Slugify();

        $locale = $this->entityManager->getRepository(Locale::class)->getDefault();
        $tagNameRepository = $this->entityManager->getRepository(TagName::class);
        foreach ($provider->getCategories() as $category) {
            $slug = $slugify->slugify($category->getName());
            if ($tagNameRepository->findOneBySlug($slug) !== null ||
                $tagNameRepository->findOneByTranslation($category->getName()) !== null
            ) {
                continue;
            }

            $tagName = new TagName();
            $tagName->setLocale($locale)
                ->setSlug($slug)
                ->setTranslation($category->getName());

            $tag = new Tag();
            $tag->addTagName($tagName)
                ->addProviderCategory($category);
            $this->entityManager->persist($tag);
        }
        $error = null;
        try {
            $this->entityManager->flush();
        } catch (\Exception $exception) {
            $error = $exception->getMessage();
        }
        return new JsonResponse(['success' => $error === null, 'error' => $error]);
    }
}
