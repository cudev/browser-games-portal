<?php

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
