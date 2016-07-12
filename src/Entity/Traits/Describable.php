<?php

namespace Ludos\Entity\Traits;

trait Describable
{
    /** @Column(type="string") */
    protected $description;

    /**
     * @param string|null $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /** @return string|null */
    public function getDescription()
    {
        return $this->description;
    }
}
