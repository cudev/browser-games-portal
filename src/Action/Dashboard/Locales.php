<?php

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
