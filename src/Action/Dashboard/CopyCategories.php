<?php

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
