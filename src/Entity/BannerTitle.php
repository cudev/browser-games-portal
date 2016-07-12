<?php

namespace Ludos\Entity;

use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Timestampable;
use Ludos\Entity\Traits\Translatable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="banner_titles")
 */
class BannerTitle
{
    use Identifiable;
    use Timestampable;
    use Translatable;

    /**
     * @ManyToOne(targetEntity="Banner", inversedBy="bannerTitles")
     */
    protected $banner;

    /**
     * @param Banner $banner
     * @return BannerTitle
     */
    public function setBanner(Banner $banner)
    {
        $this->banner = $banner;
        return $this;
    }

    /**
     * @return Banner|null
     */
    public function getBanner()
    {
        return $this->banner;
    }
}
