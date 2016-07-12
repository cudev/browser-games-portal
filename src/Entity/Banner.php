<?php

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
