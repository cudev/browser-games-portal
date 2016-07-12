<?php

namespace Ludos\Action;

use Doctrine\ORM\EntityManager;
use Ludos\Asset\HashedPackage;
use Ludos\Entity\Game\Game;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class ShowAccount
{
    protected $entityManager;
    protected $templateRenderer;
    protected $hashedPackage;
    protected $serializer;

    public function __construct(
        TemplateRendererInterface $templateRenderer,
        EntityManager $entityManager,
        HashedPackage $hashedPackage,
        Serializer $serializer
    ) {
        $this->templateRenderer = $templateRenderer;
        $this->entityManager = $entityManager;
        $this->hashedPackage = $hashedPackage;
        $this->serializer = $serializer;
    }


    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var User $user */
        $user = $request->getAttribute('user');
        $data = [
            'recommendedGames' => $this->entityManager->getRepository(Game::class)->getRecommendedForUser($user)
        ];

        return new HtmlResponse($this->templateRenderer->render('app::user', $data));
    }
}
