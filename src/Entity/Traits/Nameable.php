<?php

namespace Ludos\Entity\Traits;

trait Nameable
{
    /** @Column(type="string") */
    protected $name;

    /**
     * @param string $name
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;
        return $this;
    }

    /** @return string */
    public function getName()
    {
        return $this->name;
    }

    /** @return bool */
    public function hasName(): bool
    {
        return $this->name !== null;
    }
}
