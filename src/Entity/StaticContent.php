<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Cudev Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
