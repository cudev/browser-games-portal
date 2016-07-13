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
