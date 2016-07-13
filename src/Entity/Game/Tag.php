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

namespace Ludos\Entity\Game;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Ludos\Entity\Locale;
use Ludos\Entity\Provider\Category;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Timestampable;
use Ludos\Entity\Traits\Toggleable;

/**
 * @Entity(repositoryClass="Ludos\Entity\Repositories\TagRepository")
 * @HasLifecycleCallbacks
 * @Table(name="tags")
 */
class Tag
{
    use Identifiable;
    use Timestampable;
    use Toggleable;

    /** @ManyToMany(targetEntity="Ludos\Entity\Game\Game", mappedBy="tags", fetch="EXTRA_LAZY") */
    protected $games;

    /** @Column(type="boolean") */
    protected $featured = false;

    /** @OneToMany(targetEntity="TagName", mappedBy="tag", cascade={"persist"}) */
    protected $tagNames;

    /**
     * @ManyToMany(targetEntity="Ludos\Entity\Provider\Category", inversedBy="tags")
     * @JoinTable(
     *   name="tags_provider_categories",
     *   joinColumns={@JoinColumn(name="tag_id", referencedColumnName="id")},
     *   inverseJoinColumns={@JoinColumn(name="provider_category_id", referencedColumnName="id")}
     * )
     */
    protected $providerCategories;

    public function __construct()
    {
        $this->games = new ArrayCollection();
        $this->tagNames = new ArrayCollection();
        $this->enabled = false;
    }

    /** @return ArrayCollection|Game[] */
    public function getGames()
    {
        return $this->games;
    }

    public function addGame(Game $game)
    {
        $this->games[] = $game;
        return $this;
    }

    /**
     * @param string[] $platforms
     * @return Collection|static
     */
    public function findGamesByTypes(array $platforms)
    {
        return $this->games->filter(function (Game $game) use ($platforms) {
            return in_array($game->getType(), $platforms, true) && $game->isEnabled();
        });
    }

    /**
     * @param mixed $tagNames
     * @return Tag
     */
    public function setTagNames($tagNames)
    {
        /** @var TagName $tagName */
        foreach ($tagNames as $tagName) {
            $tagName->setTag($this);
        }
        $this->tagNames = $tagNames;
        return $this;
    }

    /**
     * @param TagName $tagNames
     * @return Tag
     */
    public function addTagName(TagName $tagName)
    {
        $tagName->setTag($this);
        $this->tagNames[] = $tagName;
        return $this;
    }

    /** @return ArrayCollection|TagName[] */
    public function getTagNames()
    {
        return $this->tagNames;
    }

    /**
     * @param bool $featured
     * @return Tag
     */
    public function setFeatured(bool $featured)
    {
        $this->featured = $featured;
        return $this;
    }

    /**
     * Shortcut for localized name
     * @see TagName::getTranslation
     * @param string $language
     * @return string name
     */
    public function getName(string $language): string
    {
        return $this->extractTranslation('translation', $language);
    }

    /**
     * Shortcut for localized slug
     * @see TagName::getSlug
     * @param string $language
     * @return string slug
     */
    public function getSlug(string $language): string
    {
        return $this->extractTranslation('slug', $language);
    }

    /**
     * Shortcut for localized slug
     * @see TagName::getSlug
     * @param string $language
     * @return string slug
     */
    public function getDescription(string $language): string
    {
        return $this->extractTranslation('description', $language);
    }

    /** @return bool */
    public function isFeatured(): bool
    {
        return $this->featured;
    }

    public function addProviderCategory(Category $category)
    {
        $this->providerCategories[] = $category;
        return $this;
    }

    /** @return ArrayCollection|Category[] */
    public function getProviderCategories()
    {
        return $this->providerCategories;
    }

    private function extractTranslation(string $translatableProperty, string $language):string
    {
        $fallbackLanguage = Locale::DEFAULT_LANGUAGE;
        $fallbackTranslation = '';
        $getterName = 'get' . ucfirst($translatableProperty);
        if (!method_exists(TagName::class, $getterName)) {
            throw new \InvalidArgumentException(sprintf('Cannot extract translation from TagName via %s', $getterName));
        }
        /** @var TagName $tagName */
        foreach ($this->tagNames as $tagName) {
            if ($tagName->getLocale()->getLanguage() === $language) {
                return (string)$tagName->{$getterName}();
            }
            if ($tagName->getLocale()->getLanguage() === $fallbackLanguage) {
                $fallbackTranslation = (string)$tagName->{$getterName}();
            }
        }
        return $fallbackTranslation;
    }
}
