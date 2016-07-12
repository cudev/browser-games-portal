<?php

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
