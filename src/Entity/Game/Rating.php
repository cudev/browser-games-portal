<?php

namespace Ludos\Entity\Game;

use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Timestampable;
use Ludos\Entity\User;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="game_ratings")
 */
class Rating
{
    use Identifiable;
    use Timestampable;

    /** @Column(type="integer") */
    protected $rating;

    /**
     * @ManyToOne(targetEntity="Ludos\Entity\User", inversedBy="ratings")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ManyToOne(targetEntity="Ludos\Entity\Game\Game", inversedBy="ratings")
     * @JoinColumn(name="game_id", referencedColumnName="id")
     */
    protected $game;

    public function __construct(User $user = null, Game $game = null)
    {
        $this->user = $user;
        $this->game = $game;
    }

    /**
     * @param mixed $rating
     * @return Rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param mixed $user
     * @return Rating
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $game
     * @return Rating
     */
    public function setGame($game)
    {
        $this->game = $game;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getGame()
    {
        return $this->game;
    }
}
