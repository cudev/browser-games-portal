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

use Aura\Session\Session;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Game\Rating;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Rate
{
    private $entityManager;
    private $session;

    public function __construct(EntityManager $entityManager, Session $session)
    {
        $this->entityManager = $entityManager;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        $parsedRequestBody = json_decode($request->getBody(), true);

        /** @var Game|null $game */
        $game = null;
        if (array_key_exists('gameId', $parsedRequestBody) && is_numeric($parsedRequestBody['gameId'])) {
            $game = $this->entityManager->getRepository(Game::class)->find($parsedRequestBody['gameId']);
        }
        if ($game === null
            || (!array_key_exists('rating', $parsedRequestBody) || !is_numeric($parsedRequestBody['rating']))
        ) {
            return new JsonResponse(null, 400);
        }

        $rating = $this
            ->entityManager
            ->getRepository(Rating::class)
            ->findOneBy([
                'user' => $user->getId(),
                'game' => $game->getId()
            ]);

        if ($rating === null) {
            $rating = new Rating($user, $game);
        }

        $rating->setRating($parsedRequestBody['rating']);
        $this->entityManager->persist($rating);
        $this->entityManager->flush();
        return new JsonResponse(['success' => true]);
    }
}
