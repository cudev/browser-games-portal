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
