<?php

namespace Ludos\Entity\Provider;

use Doctrine\Common\Collections\ArrayCollection;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Nameable;
use Ludos\Entity\Traits\Timestampable;

/**
 * @Entity(repositoryClass="Ludos\Entity\Repositories\ProviderRepository")
 * @HasLifecycleCallbacks
 * @Table(name="providers")
 */
class Provider
{
    use Timestampable;
    use Identifiable;
    use Nameable;

    /** @Column(type="string") */
    protected $uri;

    /** @OneToMany(targetEntity="Ludos\Entity\Provider\Category", mappedBy="provider", fetch="EXTRA_LAZY") */
    protected $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     */
    public function setUri(string $uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * @param Category $category
     * @return Provider
     */
    public function addCategory(Category $category)
    {
        $this->categories[] = $category;
        return $this;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }
}
