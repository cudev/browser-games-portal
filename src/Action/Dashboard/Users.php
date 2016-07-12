<?php

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
