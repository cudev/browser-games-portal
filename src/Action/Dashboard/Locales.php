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
use Doctrine\ORM\Query;
use Ludos\Entity\Locale;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\JsonResponse;

class Locales extends AbstractResourceAction
{
    protected $entityManager;
    protected $serializer;

    public function __construct(EntityManager $entityManager, Serializer $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function get(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $id = $request->getAttribute('id');
        if ($id) {
            $locale = $this->entityManager->getRepository(Locale::class)->find($id);
            if ($locale === null) {
                return $next($request, $response->withStatus(404), 'Not found');
            }
            $locales = [$locale];
        } else {
            $locales = $this->entityManager->getRepository(Locale::class)->findAll();
        }
        $normalized = [];
        foreach ($locales as $locale) {
            $normalized[] = $this->serializer->normalize($locale);
        }
        return new JsonResponse(['data' => $id ? $normalized[0] : $normalized]);
    }

    public function post(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $serializedLocale = $request->getBody()->getContents();

        $locale = $this->serializer->deserialize($serializedLocale, Locale::class, 'json');

        $this->entityManager->persist($locale);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true, 'data' => $this->serializer->normalize($locale)]);
    }

    public function patch(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        return $this->post($request, $response, $next);
    }

    public function delete(
        ServerRequestInterface $request,
        ResponseInterface $response,
        callable $next = null
    ) {
        $id = $request->getAttribute('id');
        $success = false;
        if ($id) {
            $locale = $this->entityManager->getRepository(Locale::class)->find($id);
            $this->entityManager->remove($locale);
            $this->entityManager->flush();
            $success = true;
        }
        return new JsonResponse(['success' => $success]);
    }
}
