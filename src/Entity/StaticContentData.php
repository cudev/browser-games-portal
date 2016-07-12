<?php

namespace Ludos\Entity;

use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Timestampable;
use Ludos\Entity\Traits\Translatable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="static_content_data")
 */
class StaticContentData
{
    use Identifiable;
    use Timestampable;
    use Translatable;

    /**
     * @ManyToOne(targetEntity="StaticContent", inversedBy="staticContentData")
     */
    protected $staticContent;

    /**
     * @param StaticContent $staticContent
     * @return StaticContentData
     */
    public function setStaticContent(StaticContent $staticContent)
    {
        $this->staticContent = $staticContent;
        return $this;
    }

    /**
     * @return StaticContent|null
     */
    public function getStaticContent()
    {
        return $this->staticContent;
    }
}
