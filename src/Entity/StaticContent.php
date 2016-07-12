<?php

namespace Ludos\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Timestampable;

/**
 * @Entity(repositoryClass="Ludos\Entity\Repositories\StaticContentRepository")
 * @HasLifecycleCallbacks
 * @Table(name="static_content")
 */
class StaticContent
{
    use Identifiable;
    use Timestampable;

    /** @OneToMany(targetEntity="StaticContentData", mappedBy="staticContent", cascade={"persist"}) */
    protected $staticContentData;

    /** @Column(type="string") */
    protected $accessKey;

    /** @Column(type="string") */
    protected $pageName;

    public function __construct()
    {
        $this->staticContentData = new ArrayCollection();
    }

    /**
     * @param mixed $accessKey
     * @return StaticContent
     */
    public function setAccessKey($accessKey)
    {
        $this->accessKey = $accessKey;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccessKey()
    {
        return $this->accessKey;
    }

    /**
     * @param mixed $pageName
     * @return StaticContent
     */
    public function setPageName($pageName)
    {
        $this->pageName = $pageName;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPageName()
    {
        return $this->pageName;
    }

    /**
     * @return StaticContentData[]
     */
    public function getStaticContentData()
    {
        return $this->staticContentData;
    }

    /**
     * @param StaticContentData $staticContentData
     * @return $this
     */
    public function addStaticContentData(StaticContentData $staticContentData)
    {
        $staticContentData->setStaticContent($this);
        $this->staticContentData[] = $staticContentData;
        return $this;
    }
}
