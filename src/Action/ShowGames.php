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
use Ludos\Entity\Game\TagName;
use Ludos\Entity\Repositories\BannerRepository;
use Ludos\Entity\Repositories\GameRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template\TemplateRendererInterface;

class ShowGames
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
        $tagSlug = $request->getAttribute('tagSlug', 'all');
        $order = $request->getAttribute('order', 'top');
        $page = $request->getAttribute('page', 1);

        /** @var GameRepository $gameRepository */
        $gameRepository = $this->entityManager->getRepository(Game::class);
        /** @var BannerRepository $bannerRepository */
        $bannerRepository = $this->entityManager->getRepository(Banner::class);

        $gameTypes = $request->getAttribute(DetectSupportedGames::class, null);

        $tag = null;
        $banner = null;
        $games = null;
        if ($tagSlug !== null && $tagSlug !== 'all') {
            /** @var TagName|null $tagName */
            $tagName = $this->entityManager->getRepository(TagName::class)->findOneBy(['slug' => $tagSlug]);
            if ($tagName === null) {
                return $next($request, $response->withStatus(404), 'Not found');
            }
            $tag = $tagName->getTag();
            $games = $gameRepository->paginateActiveByTag($tag, $gameTypes, $order, $page);
            $banner = $bannerRepository->findOneByTag($tag);
        } else {
            $games = $gameRepository->paginateActiveByTag(null, $gameTypes, $order, $page);
            $banner = $bannerRepository->findTop();
        }

        /** @var User|null $user */
        $user = $request->getAttribute('user');

        $data = [
            'banner' => $banner,
            'serializedUser' => $this->serializer->serialize($user, 'json'),
            'user' => $user,
            'games' => $games,
            'tag' => $tag,
            'order' => $order,
            'canonical' => $request->getUri()
        ];
        return new HtmlResponse($this->templateRenderer->render('app::games', $data));
    }
}
