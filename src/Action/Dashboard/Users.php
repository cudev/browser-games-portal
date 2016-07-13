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

use Carbon\Carbon;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\SocialNetworkConnection;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\Serializer\Serializer;
use Zend\Diactoros\Response\JsonResponse;

class Users extends AbstractResourceAction
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
        $mapConnectionNames = function (SocialNetworkConnection $connection) {
            return $connection->getName();
        };

        $dateNow = new Carbon();
        $currentYearFormat = 'd M';
        $lastYearFormat = $currentYearFormat . ' Y';

        /** @var User $user */
        foreach ($this->entityManager->getRepository(User::class)->findAll() as $user) {
            /** @var array $normalizedUser */
            $normalizedUser = $this->serializer->normalize($user);

            $normalizedUser['connections'] = $user->getSocialNetworkConnections()
                ->map($mapConnectionNames)
                ->toArray();

            $normalizedUser['isSubscribed'] = $user->isSubscribed();
            $normalizedUser['isEmailConfirmed'] = $user->isEmailConfirmed();
            $normalizedUser['totalGamesPlayed'] = $user->getPlayActivityEntries()->count();
            $normalizedUser['totalGamesRated'] = $user->getRatings()->count();
            $normalizedUser['totalComments'] = $user->getComments()->count();
            $normalizedUser['joined'] = $user->getCreatedAt()->format(
                $dateNow->year - $user->getCreatedAt()->year === 0
                    ? $currentYearFormat
                    : $lastYearFormat
            );

            $normalizedUsers[] = $normalizedUser;
        }
        return new JsonResponse(['data' => $normalizedUsers]);
    }
}
