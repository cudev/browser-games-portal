<?php

namespace Ludos\Action\Dashboard;

use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Tag;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\JsonResponse;

class Tags extends AbstractResourceAction
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
        $id = $request->getAttribute('id');
        if ($id) {
            $tag = $this->entityManager->getRepository(Tag::class)->find($id);
            if ($tag === null) {
                return $next($request, $response->withStatus(404), 'Not found');
            }
            $tags = [$tag];
        } else {
            $tags = $this->entityManager->getRepository(Tag::class)->findAll();
        }

        $normalized = [];
        foreach ($tags as $tag) {
            $normalized[] = $this->serializer->normalize($tag);
        }

        return new JsonResponse(['data' => $id === null ? $normalized : $normalized[0]]);
    }

    public function post(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $normalizedTag = $request->getBody()->getContents();

        $tag = $this->serializer->deserialize($normalizedTag, Tag::class, 'json');

        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $this->get($request, $response);
    }

    public function patch(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        return $this->post($request, $response, $next);
    }

    public function delete(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $id = $request->getAttribute('id');

        if (!$id) {
            return $next($request, $response->withStatus(404), 'Not found');
        }

        /** @var Tag $tag */
        $tag = $this->entityManager->getRepository(Tag::class)->find($id);
        $this->entityManager->remove($tag);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }
}
