<?php

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
