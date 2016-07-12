<?php

namespace Ludos\Entity\Game;

use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Timestampable;
use Ludos\Entity\Traits\Translatable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="game_descriptions")
 */
class Description
{
    use Identifiable;
    use Timestampable;
    use Translatable;

    /**
     * @ManyToOne(targetEntity="Game", inversedBy="descriptions")
     * @JoinColumn(name="game_id", referencedColumnName="id")
     */
    protected $game;

    /**
     * @param Game $game
     * @return Description
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
