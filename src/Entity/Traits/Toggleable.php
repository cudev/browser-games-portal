<?php

namespace Ludos\Entity\Traits;

trait Toggleable
{
    /** @Column(type="boolean") */
    protected $enabled = false;

    /**
     * @param boolean $enabled
     * @return $this
     */
    public function setEnabled(bool $enabled)
    {
        $this->enabled = $enabled;
        return $this;
    }

    /** @return boolean */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /** @return $this */
    public function enable()
    {
        $this->enabled = true;
        return $this;
    }

    /** @return $this */
    public function disable()
    {
        $this->enabled = false;
        return $this;
    }

    /** @return $this */
    public function toggle()
    {
        $this->enabled = !$this->enabled;
        return $this;
    }
}
