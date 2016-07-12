<?php

namespace Ludos\Entity\Game;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Ludos\Entity\AbstractEntity;
use Ludos\Entity\Banner;
use Ludos\Entity\PlayActivityEntry;
use Ludos\Entity\Provider\Category;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Nameable;
use Ludos\Entity\Traits\Timestampable;
use Ludos\Entity\Traits\Toggleable;

/**
 * @Entity(repositoryClass="Ludos\Entity\Repositories\GameRepository")
 * @HasLifecycleCallbacks
 * @Table(name="games")
 */
class Game
{
    use Identifiable;
    use Nameable;
    use Timestampable;
    use Toggleable;

    const HTML5 = 'html5';
    const UNITY = 'unity';
    const FLASH = 'flash';

    /** @Column(type="string") */
    protected $slug;

    /** @Column(type="string") */
    protected $type;

    /** @Column(type="string") */
    protected $url;

    /** @OneToMany(targetEntity="Description", mappedBy="game", cascade={"persist"}, indexBy="locale") */
    protected $descriptions;

    /** @ManyToMany(targetEntity="Ludos\Entity\User", mappedBy="bookmarkedGames") */
    protected $bookmarkedUsers;

    /** @Column(type="integer") */
    protected $width;

    /** @Column(type="integer") */
    protected $height;

    /** @Column(type="integer") */
    protected $plays;

    /** @OneToMany(targetEntity="Ludos\Entity\Game\Meta", mappedBy="game") */
    protected $meta;

    /**
     * @OneToMany(targetEntity="Ludos\Entity\Game\Rating", mappedBy="game")
     * @OrderBy({"rating" = "ASC"})
     */
    protected $ratings;

    /** @OneToMany(targetEntity="Ludos\Entity\Comment", mappedBy="game") */
    protected $comments;

    /** @OneToMany(targetEntity="Ludos\Entity\PlayActivityEntry", mappedBy="game") */
    protected $playActivityEntries;

    // todo: change for filesystem
    /** @Column(type="string") */
    protected $thumbnail;

    /**
     * @OneToOne(targetEntity="Ludos\Entity\Banner", mappedBy="game")
     */
    protected $banner;

    /**
     * @ManyToMany(targetEntity="Ludos\Entity\Game\Tag", inversedBy="games")
     * @JoinTable(
     *   name="games_tags",
     *   joinColumns={@JoinColumn(name="game_id", referencedColumnName="id")},
     *   inverseJoinColumns={@JoinColumn(name="tag_id", referencedColumnName="id")}
     * )
     */
    protected $tags;

    /**
     * This relation isn't stored in DB
     * @var Category[]
     */
    protected $categories;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->tags = new ArrayCollection();
        $this->descriptions = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->plays = 0;
        $this->enabled = false;
        $this->meta = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    /**
     * @param string|null $type
     * @return Game
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     */
    public function setHeight($height)
    {
        $this->height = $height;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return Description[]|ArrayCollection|null
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * @param Description[] $descriptions
     */
    public function setDescriptions($descriptions)
    {
        foreach ($descriptions as $description) {
            $description->setGame($this);
        }
        $this->descriptions = $descriptions;
        return $this;
    }

    public function addDescription(Description $description)
    {
        $description->setGame($this);
        $this->descriptions[] = $description;
        return $this;
    }

    public function getDescription(string $language): string
    {
        $fallbackLanguage = 'en';
        $fallbackTranslation = '';
        /** @var Description $description */
        foreach ($this->descriptions as $description) {
            if ($description->getLocale()->getLanguage() === $language) {
                return $description->getTranslation();
            }
            if ($description->getLocale()->getLanguage() === $fallbackLanguage) {
                $fallbackTranslation = $description->getTranslation();
            }
        }
        return $fallbackTranslation;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string|null $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
        return $this;
    }

    /**
     * @param array $tags
     * @return Game
     */
    public function setTags($tags)
    {
        /** @var Tag $tag */
        foreach ($tags as $tag) {
            $tag->addGame($this);
        }
        $this->tags = $tags;
        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getTags()
    {
        return $this->tags;
    }

    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
        return $this;
    }

    /**
     * @param mixed $thumbnail
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param mixed $ratings
     * @return Game
     */
    public function setRatings($ratings)
    {
        $this->ratings = $ratings;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getRatings()
    {
        return $this->ratings;
    }

    public function getAverageRating()
    {
        $ratings = $this->getRatings();
        $total = 0;
        foreach ($ratings as $rating) {
            $total += $rating->getRating();
        }
        return $total === 0 ? $total : ceil($total / $ratings->count());
    }

    /** @return int|null */
    public function getWorstRating()
    {
        /** @var Rating $rating */
        $rating = $this->ratings->get(0);
        return $rating !== null ? $rating->getRating() : null;
    }

    /** @return int|null */
    public function getBestRating()
    {
        /** @var Rating $rating */
        $rating = $this->ratings->get($this->ratings->count() - 1);
        return $rating !== null ? $rating->getRating() : null;
    }

    /**
     * @param mixed $comments
     * @return Game
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    /**
     * @return ArrayCollection|null
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param int $plays
     * @return Game
     */
    public function setPlays(int $plays)
    {
        $this->plays = $plays;
        return $this;
    }

    /**
     * @return int
     */
    public function getPlays(): int
    {
        return $this->plays;
    }

    /**
     * @return $this
     */
    public function incrementPlays()
    {
        $this->setPlays($this->getPlays() + 1);
        return $this;
    }

    /**
     * @param Meta[] $meta
     * @return Game
     */
    public function setMeta($meta)
    {
        foreach ($meta as $item) {
            $item->setGame($this);
        }
        $this->meta = $meta;
        return $this;
    }

    public function addMeta(Meta $meta)
    {
        $meta->setGame($this);
        $this->meta[] = $meta;
        return $this;
    }

    /**
     * @return Meta[]
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param $categories
     * @return Game
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
        return $this;
    }

    /**
     * @return Category[]|ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    public function addCategory(Category $category)
    {
        $this->categories[] = $category;
        return $this;
    }

    /**
     * @param Banner $banner
     * @return Game
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

    /**
     * @return PlayActivityEntry[]
     */
    public function getPlayActivityEntries()
    {
        return $this->playActivityEntries;
    }

    /**
     * @param PlayActivityEntry $playActivityEntry
     */
    public function addPlayActivityEntries(PlayActivityEntry $playActivityEntry)
    {
        $this->playActivityEntries[] = $playActivityEntry;
        return $this;
    }

    public static function getMobileSupportedTypes(): array
    {
        return [self::HTML5];
    }

    // TODO: Linux systems don't support Unity plugin, so we need to detect it also
    public static function getDesktopSupportedTypes(): array
    {
        return [self::HTML5, self::FLASH, self::UNITY];
    }

    /**
     * Method is used in templates to help generate meta data
     * @link https://developers.google.com/structured-data/rich-snippets/sw-app
     * @return array
     */
    public function getSupportedOperatingSystems(): array
    {
        switch ($this->getType()) {
            case self::FLASH:
                return ['Windows', 'Linux'];
            case self::HTML5:
                return ['Windows', 'Linux', 'OSX', 'iOS', 'Chrome OS', 'Android'];
            case self::UNITY:
                return ['Windows'];
            default:
                return [];
        }
    }
}
