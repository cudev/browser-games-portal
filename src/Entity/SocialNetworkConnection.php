<?php

namespace Ludos\Entity;

use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Nameable;
use Ludos\Entity\Traits\Timestampable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="social_network_connections")
 */
class SocialNetworkConnection
{
    use Identifiable;
    use Timestampable;
    use Nameable;

    const FACEBOOK = 'facebook';
    const TWITTER = 'twitter';
    const GOOGLE = 'google';

    /** @ManyToOne(targetEntity="Ludos\Entity\User", inversedBy="socialNetworkConnections") */
    protected $user;

    /** @Column(type="string") */
    protected $remoteUserId;

    public function __construct(string $name, User $user = null, string $remoteUserId = null)
    {
        $this->user = $user;
        $this->name = $name;
        $this->remoteUserId = $remoteUserId;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string|null
     */
    public function getRemoteUserId()
    {
        return $this->remoteUserId;
    }

    /**
     * @param User $user
     * @return SocialNetworkConnection
     */
    public function setUser(User $user)
    {
        $user->addSocialNetworkConnection($this);
        $this->user = $user;
        return $this;
    }

    /**
     * @param string $remoteUserId
     * @return SocialNetworkConnection
     */
    public function setRemoteUserId(string $remoteUserId)
    {
        $this->remoteUserId = $remoteUserId;
        return $this;
    }
}
