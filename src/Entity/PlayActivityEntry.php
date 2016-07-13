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
