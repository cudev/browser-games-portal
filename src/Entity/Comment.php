<?php

namespace Ludos\Entity;

use Ludos\Entity\Game\Game;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Timestampable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="comments")
 */
class Comment
{
    use Identifiable;
    use Timestampable;

    /**
     * @ManyToOne(targetEntity="\Ludos\Entity\Locale")
     * @JoinColumn(name="locale_id", referencedColumnName="id")
     */
    protected $locale;

    /**
     * @ManyToOne(targetEntity="Ludos\Entity\Game\Game", inversedBy="comments")
     * @JoinColumn(name="game_id", referencedColumnName="id")
     */
    protected $game;

    /**
     * @ManyToOne(targetEntity="Ludos\Entity\User", inversedBy="comments")
     * @JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /** @Column(type="string") */
    protected $body;

    /**
     * @param mixed $locale
     * @return Comment
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param mixed $body
     * @return Comment
     */
    public function setBody($body)
    {
        $this->body = $body;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @param mixed $user
     * @return Comment
     */
    public function setUser($user)
    {
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

    /**
     * @param mixed $game
     * @return Comment
     */
    public function setGame(Game $game)
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
