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

use Ludos\Asset\HashedPackage;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Timestampable;
use Ludos\Entity\Traits\Toggleable;

/**
 * @Entity(repositoryClass="Ludos\Entity\Repositories\BannerRepository")
 * @HasLifecycleCallbacks
 * @Table(name="banners")
 */
class Banner
{
    use Identifiable;
    use Timestampable;
    use Toggleable;

    /** @Column(type="string") */
    protected $picture;

    /** @OneToOne(targetEntity="Ludos\Entity\Game\Game", inversedBy="banner") */
    protected $game;

    /** @Column(type="integer") */
    protected $priority = 0;

    /** @OneToMany(targetEntity="BannerTitle", mappedBy="banner", cascade={"persist"}) */
    protected $bannerTitles;

    /**
     * @param string|null $picture
     * @return Banner
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
        return $this;
    }

    /** @return string|null */
    public function getPicture()
    {
        return $this->picture;
    }

    /** @return bool */
    public function hasPicture()
    {
        return (bool)$this->picture;
    }

    /**
     * @param Game $game
     * @return Banner
     */
    public function setGame(Game $game)
    {
        $game->setBanner($this);
        $this->game = $game;
        return $this;
    }

    /**
     * @return Game|null
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param int $priority
     * @return Banner
     */
    public function setPriority(int $priority)
    {
        $this->priority = $priority;
        return $this;
    }

    /**
     * @return int
     */
    public function getPriority(): int
    {
        return $this->priority;
    }

    /**
     * @param BannerTitle $bannerTitle
     * @return $this
     */
    public function addBannerTitle(BannerTitle $bannerTitle)
    {
        $bannerTitle->setBanner($this);
        $this->bannerTitles[] = $bannerTitle;
        return $this;
    }

    /**
     * @return BannerTitle[]|null
     */
    public function getBannerTitles()
    {
        return $this->bannerTitles;
    }

    /**
     * Shortcut for localized title
     * @see BannerTitle::getTranslation
     * @param string $language
     * @return string title
     */
    public function getTitle(string $language)
    {
        $fallbackLanguage = 'en';
        $fallbackTranslation = '';
        /** @var BannerTitle $bannerTitle */
        foreach ($this->bannerTitles as $bannerTitle) {
            if ($bannerTitle->getLocale()->getLanguage() === $language) {
                return $bannerTitle->getTranslation();
            }
            if ($bannerTitle->getLocale()->getLanguage() === $fallbackLanguage) {
                $fallbackTranslation = $bannerTitle->getTranslation();
            }
        }
        return $fallbackTranslation;
    }

    // TODO: this method shouldn't be here
    public function getPictureUrl(): string
    {
        if (!$this->picture) {
            return '';
        }
        return '/uploads/' . HashedPackage::getSubdirectories($this->picture) . $this->picture;
    }
}
