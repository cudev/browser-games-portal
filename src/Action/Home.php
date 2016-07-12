<?php

namespace Ludos\Action;

use Doctrine\ORM\EntityManager;
use Ludos\Entity\Banner;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Repositories\GameRepository;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class Home
{
    protected $entityManager;
    protected $templateRenderer;
    protected $serializer;

    public function __construct(
        TemplateRendererInterface $templateRenderer,
        EntityManager $entityManager,
        Serializer $serializer
    ) {
        $this->templateRenderer = $templateRenderer;
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var User|null $user */
        $user = $request->getAttribute('user');
        /** @var GameRepository $gameRepository */
        $gameRepository = $this->entityManager->getRepository(Game::class);

        $banner = $this->entityManager->getRepository(Banner::class)->findOneBy(
            ['enabled' => true],
            ['priority' => 'DESC']
        );
        $gameTypes = $request->getAttribute(DetectSupportedGames::class, null);
        $params = [
            'banner' => $banner,
            'topGames' => $gameRepository->findTop($gameTypes),
            'newGames' => $gameRepository->findNew($gameTypes),
            'popularGames' => $gameRepository->findPopular($gameTypes),
            'discussedGames' => $gameRepository->findDiscussed($gameTypes),
            'user' => $user,
            'serializedUser' => $this->serializer->serialize($user, 'json'),
            'supportedGameTypes' => $request->getAttribute(DetectSupportedGames::class, []),
            'canonical' => $request->getUri()
        ];
        return new HtmlResponse($this->templateRenderer->render('app::home', $params));
    }
}
