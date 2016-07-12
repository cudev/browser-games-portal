<?php

namespace Ludos\Entity\Traits;

trait Identifiable
{
    /**
     * @Id @Column(type="integer")
     * @GeneratedValue
     */
    protected $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }
}
