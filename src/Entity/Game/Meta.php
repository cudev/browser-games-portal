<?php

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
