<?php

namespace Ludos\Entity;

use Ludos\Entity\Game\Game;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Timestampable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="play_activity_entries")
 */
class PlayActivityEntry
{
    use Identifiable;
    use Timestampable;

    /** @ManyToOne(targetEntity="Ludos\Entity\User", inversedBy="playActivityEntries") */
    protected $user;

    /** @ManyToOne(targetEntity="Ludos\Entity\Game\Game", inversedBy="playActivityEntries") */
    protected $game;

    public function __construct(User $user = null, Game $game = null)
    {
        $this->user = $user;
        $this->game = $game;
    }

    /**
     * @param Game $game
     * @return PlayActivityEntry
     */
    public function setGame(Game $game)
    {
        $game->addPlayActivityEntries($this);
        $this->game = $game;
        return $this;
    }

    /**
     * @return Game|null
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param User $user
     * @return PlayActivityEntry
     */
    public function setUser(User $user)
    {
        $user->addPlayActivityEntries($this);
        $this->user = $user;
        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }
}
