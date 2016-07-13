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

namespace Ludos\Entity\Game;

use Ludos\Entity\Provider\Provider;
use Ludos\Entity\Traits\Timestampable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="games_meta")
 */
class Meta
{
    use Timestampable;

    /** @Id @Column(type="string") */
    protected $id;

    /** @ManyToOne(targetEntity="Ludos\Entity\Provider\Provider") */
    protected $provider;

    /** @ManyToOne(targetEntity="Ludos\Entity\Game\Game", inversedBy="meta", cascade={"persist"}) */
    protected $game;

    /** @Column(type="string") */
    protected $data;

    /**
     * @param Provider $provider
     * @return Meta
     */
    public function setProvider(Provider $provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @return Provider|null
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @param Game $game
     * @return Meta
     */
    public function setGame(Game $game)
    {
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
     * @param string $id
     * @return Meta
     */
    public function setId(string $id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $data
     * @return Meta
     */
    public function setData(string $data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getData()
    {
        return $this->data;
    }
}
