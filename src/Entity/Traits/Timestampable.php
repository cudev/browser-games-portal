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

namespace Ludos\Entity\Traits;

use Carbon\Carbon;
use DateTime;

trait Timestampable
{
    /**
     * @var DateTime $createdAt
     *
     * @Column(type="datetime")
     */
    protected $createdAt;

    /**
     * @var DateTime $updatedAt
     *
     * @Column(type="datetime")
     */
    protected $updatedAt;


    /**
     * @return Carbon
     */
    public function getCreatedAt()
    {
        return $this->carbonate($this->createdAt);
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @param DateTime|null $dateTime
     * @return Carbon|null
     */
    private function carbonate($dateTime)
    {
        switch (true) {
            case $dateTime instanceof Carbon:
                return $dateTime;
            case $dateTime instanceof DateTime:
                return Carbon::instance($dateTime);
            default:
                return $dateTime;
        }
    }

    /**
     * @return DateTime
     */
    public function getUpdatedAt()
    {
        return $this->carbonate($this->updatedAt);
    }

    /**
     * @param DateTime $updatedAt
     */
    public function setUpdatedAt(DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @PrePersist
     * @PreUpdate
     * @return $this
     */
    public function updateTimestamps()
    {
        $this->setUpdatedAt(new DateTime());
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new DateTime());
        }
        return $this;
    }
}
