<?php

namespace Ludos\Action;

use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManager;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use Ludos\Asset\HashedPackage;
use Ludos\Entity\User;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class DeleteUserPicture
{
    private $entityManager;
    private $filesystem;

    public function __construct(EntityManager $entityManager, Filesystem $filesystem)
    {
        $this->entityManager = $entityManager;
        $this->filesystem = $filesystem;
    }

    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        /** @var User $user */
        $user = $request->getAttribute('user');

        $picture = $user->getPicture();

        if ($picture) {
            // todo: delete empty folders

            $criteria = new Criteria();
            $criteria->where(Criteria::expr()->eq('picture', $picture))
                ->andWhere(Criteria::expr()->neq('id', $user->getId()));

            /** @var User $userWithSamePicture */
            $usersWithSamePicture = $this->entityManager->getRepository(User::class)->matching($criteria);

            if (count($usersWithSamePicture) === 0) {
                try {
                    $this->filesystem->delete(HashedPackage::getSubdirectories($picture) . $picture);
                } catch (FileNotFoundException $exception) {
                    // it's okay, do nothing
                }
            }

            $user->setPicture(null);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return new JsonResponse(['success' => true]);
    }
}
