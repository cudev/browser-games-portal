<?php

namespace Ludos\Entity\Game;

use Ludos\Entity\Traits\Describable;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Timestampable;
use Ludos\Entity\Traits\Translatable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="tag_names")
 */
class TagName
{
    use Identifiable;
    use Timestampable;
    use Translatable;
    use Describable;

    /**
     * @ManyToOne(targetEntity="Tag", inversedBy="tagNames")
     * @JoinColumn(name="tag_id", referencedColumnName="id")
     */
    protected $tag;

    /** @Column(type="string") */
    protected $slug;

    /**
     * @param string|null $slug
     * @return TagName
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /** @return string|null */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $tag
     * @return TagName
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

    /** @return Tag|null */
    public function getTag()
    {
        return $this->tag;
    }
}
