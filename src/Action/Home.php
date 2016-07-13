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
