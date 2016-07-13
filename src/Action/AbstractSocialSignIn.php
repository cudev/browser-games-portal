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
use Ludos\Entity\Role;
use Ludos\Entity\SocialNetworkConnection;
use Ludos\Entity\User;

abstract class AbstractSocialSignIn
{
    /** @var EntityManager */
    protected $entityManager;
    /** @var Session */
    protected $session;

    const REFERER = 'referer';

    public function sign(string $email, string $name, string $remoteId, string $connectionName)
    {
        /** @var User $user */
        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if ($user !== null) {
            $connection = $user->getSocialNetworkConnection($connectionName);
            if ($connection === null) {
                $connection = new SocialNetworkConnection($connectionName);
                $connection->setUser($user)->setRemoteUserId($remoteId);
            }
            $connection->updateTimestamps();
        } else {
            $role = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => Role::USER]);

            $user = new User();
            $user->setEmail($email)
                ->setName($name)
                ->setRole($role);

            $connection = new SocialNetworkConnection($connectionName);
            $connection->setUser($user)->setRemoteUserId($remoteId);

            $this->entityManager->persist($user);
        }

        $this->entityManager->persist($connection);
        $this->entityManager->flush();

        $this->session->getSegment(User::class)->set('userId', $user->getId());
        $this->session->commit();
    }
}
