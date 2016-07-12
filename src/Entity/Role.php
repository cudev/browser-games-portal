<?php

namespace Ludos\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Nameable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="roles")
 */
class Role
{
    use Identifiable;
    use Nameable;

    const ADMIN = 'admin';
    const USER = 'user';
    const SUBSCRIBER = 'subscriber';

    /** @OneToMany(targetEntity="Ludos\Entity\User", mappedBy="role") */
    protected $users;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @param mixed $users
     * @return Role
     */
    public function setUsers($users)
    {
        $this->users = $users;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->users;
    }

    public function addUser(User $user)
    {
        $this->users[] = $user;
        return $this;
    }
}
