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
use Doctrine\ORM\EntityRepository;
use Ludos\Entity\Game\Tag;
use Ludos\Entity\Provider\Category;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\JsonResponse;

class Categories extends AbstractResourceAction
{
    protected $entityManager;
    protected $serializer;

    public function __construct(EntityManager $entityManager, Serializer $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function get(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $categoryId = $request->getAttribute('categoryId');
        /** @var EntityRepository $categoryRepository */
        $categoryRepository = $this->entityManager->getRepository(Category::class);
        if ($categoryId) {
            $category = $categoryRepository->find($categoryId);
            if ($category === null) {
                return $next($request, $response->withStatus(404), 'Not found');
            }
            $categories = [$category];
        } else {
            $categories = $categoryRepository->findAll();
        }

        $normalizedCategories = [];
        foreach ($categories as $category) {
            $normalizedCategories[] = $this->serializer->normalize($category);
        }

        $tags = $this->entityManager->getRepository(Tag::class)->findAll();
        $normalizedTags = [];
        foreach ($tags as $tag) {
            $normalizedTags[] = $this->serializer->normalize($tag);
        }

        return new JsonResponse(['data' => $normalizedCategories, 'included' => ['tags' => $normalizedTags]]);
    }


    public function delete(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {

    }

    public function post(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {

    }

    public function patch(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $serializedCategory = $request->getBody()->getContents();

        /** @var Category $category */
        $category = $this->serializer->deserialize($serializedCategory, Category::class, 'json');

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return new JsonResponse(['data' => $serializedCategory]);
    }
}
