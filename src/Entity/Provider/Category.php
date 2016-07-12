<?php

namespace Ludos\Entity\Provider;

use Ludos\Entity\Game\Tag;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Nameable;
use Ludos\Entity\Traits\Timestampable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="provider_categories")
 */
class Category
{
    use Identifiable;
    use Nameable;
    use Timestampable;

    /**
     * @ManyToOne(targetEntity="Provider", inversedBy="categories")
     * @JoinColumn(name="provider_id", referencedColumnName="id")
     */
    protected $provider;

    /** @ManyToMany(targetEntity="Ludos\Entity\Game\Tag", mappedBy="providerCategories") */
    protected $tags;

    /**
     * @param mixed $provider
     * @return Category
     */
    public function setProvider(Provider $provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getProvider()
    {
        return $this->provider;
    }

    /**
     * @return array|null
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param mixed $tags
     */
    public function setTags($tags)
    {
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $tag->addProviderCategory($this);
        }
        $this->tags = $tags;
    }

    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
        return $this;
    }
}
