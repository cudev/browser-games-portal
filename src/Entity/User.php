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

use Carbon\Carbon;
use Cudev\OrdinaryMail\CredentialsInterface;
use Doctrine\Common\Collections\ArrayCollection;
use InvalidArgumentException;
use Ludos\Asset\HashedPackage;
use Ludos\Entity\Game\Game;
use Ludos\Entity\Game\Rating;
use Ludos\Entity\Traits\Identifiable;
use Ludos\Entity\Traits\Nameable;
use Ludos\Entity\Traits\Timestampable;
use Ludos\Entity\Traits\Toggleable;

/**
 * @Entity
 * @HasLifecycleCallbacks
 * @Table(name="users")
 */
class User implements CredentialsInterface
{
    use Timestampable;
    use Identifiable;
    use Nameable;
    use Toggleable;

    const MALE = 'male';
    const FEMALE = 'female';

    /** @Column(type="string") */
    protected $email;

    /** @Column(type="boolean") */
    protected $emailConfirmed;
//    protected $illuminatiConfirmed;

    /** @Column(type="string") */
    protected $passwordHash;

    /** @OneToMany(targetEntity="Ludos\Entity\Game\Rating", mappedBy="user", fetch="EXTRA_LAZY") */
    protected $ratings;

    /** @OneToMany(targetEntity="Ludos\Entity\Comment", mappedBy="user", fetch="EXTRA_LAZY") */
    protected $comments;

    /**
     * @ManyToMany(targetEntity="Ludos\Entity\Game\Game", inversedBy="bookmarkedUsers", fetch="EXTRA_LAZY")
     * @JoinTable(
     *   name="users_bookmarked_games",
     *   joinColumns={@JoinColumn(name="user_id", referencedColumnName="id")},
     *   inverseJoinColumns={@JoinColumn(name="game_id", referencedColumnName="id")}
     * )
     */
    protected $bookmarkedGames;

    /** @Column(type="boolean") */
    protected $subscribed;

    /** @Column(type="datetime") */
    protected $birthday;

    /** @Column(type="string") */
    protected $gender;

    /** @Column(type="string") */
    protected $picture;

    /** @OneToMany(targetEntity="Ludos\Entity\PlayActivityEntry", mappedBy="user", fetch="EXTRA_LAZY") */
    protected $playActivityEntries;

    /** @OneToMany(targetEntity="Ludos\Entity\SocialNetworkConnection", mappedBy="user") */
    protected $socialNetworkConnections;

    /** @ManyToOne(targetEntity="Ludos\Entity\Role", inversedBy="users") */
    protected $role;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->bookmarkedGames = new ArrayCollection();
        $this->playActivityEntries = new ArrayCollection();
        $this->socialNetworkConnections = new ArrayCollection();
        $this->gender = self::MALE;
        $this->subscribed = false;
        $this->enabled = false;
        $this->emailConfirmed = false;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email)
    {
        $this->email = strtolower($email);
        return $this;
    }

    /** @return string|null */
    public function getEmail()
    {
        return $this->email;
    }

    /** @return string|null */
    public function getAddress()
    {
        return $this->getEmail();
    }

    /**
     * @param string $passwordHash
     * @return User
     */
    public function setPasswordHash(string $passwordHash)
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    /** @return string|null */
    public function getPasswordHash()
    {
        return $this->passwordHash;
    }

    /** @return bool */
    public function hasPasswordHash(): bool
    {
        return $this->passwordHash !== null;
    }

    /**
     * @param string $password
     * @return bool
     */
    public function passwordVerify(string $password)
    {
        return password_verify($password, $this->passwordHash);
    }

    /**
     * @param Rating[] $ratings
     * @return User
     */
    public function setRatings($ratings)
    {
        $this->ratings = $ratings;
        return $this;
    }

    /** @return Rating[]|ArrayCollection */
    public function getRatings()
    {
        return $this->ratings;
    }

    /**
     * @param Game $game
     * @return bool
     */
    public function hasBookmarkedGame(Game $game)
    {
        return $this->getBookmarkedGames()->contains($game);
    }

    /** @return bool */
    public function hasBookmarkedGames(): bool
    {
        return $this->getBookmarkedGames()->count() > 0;
    }

    /**
     * @param Game $favouriteGame
     * @return User
     */
    public function addBookmarkedGame(Game $favouriteGame)
    {
        $this->bookmarkedGames[] = $favouriteGame;
        return $this;
    }

    /**
     * @param Game $game
     * @return bool
     */
    public function removeBookmarkedGame(Game $game)
    {
        return $this->bookmarkedGames->removeElement($game);
    }

    /**
     * @param Game $game
     * @return void
     */
    public function toggleBookmarkedGame(Game $game)
    {
        if ($this->hasBookmarkedGame($game)) {
            $this->removeBookmarkedGame($game);
        } else {
            $this->addBookmarkedGame($game);
        }
    }

    /** @return Game[]|ArrayCollection */
    public function getBookmarkedGames()
    {
        return $this->bookmarkedGames;
    }

    /**
     * @param boolean $subscribed
     * @return User
     */
    public function setSubscribed(bool $subscribed)
    {
        $this->subscribed = $subscribed;
        return $this;
    }

    /** @return bool */
    public function isSubscribed()
    {
        return $this->subscribed;
    }

    /**
     * @param mixed $birthday
     * @return User
     */
    public function setBirthday($birthday)
    {
        if (is_string($birthday)) {
            $birthday = Carbon::parse($birthday);
        }
        $this->birthday = $birthday;
        return $this;
    }

    /** @return Carbon|null */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param string $gender
     * @return User
     * @throws InvalidArgumentException
     */
    public function setGender(string $gender)
    {
        $gender = strtolower($gender);
        if ($gender !== self::MALE && $gender !== self::FEMALE) {
            throw new InvalidArgumentException();
        }
        $this->gender = $gender;
        return $this;
    }

    /** @return string|null */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $password
     * @return bool|string
     */
    public static function makePasswordHash(string $password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * @param Comment[] $comments
     * @return User
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
        return $this;
    }

    /** @return Comment[]|ArrayCollection */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param string|null $picture
     * @return User
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

    // TODO: this method shouldn't be here
    public function getPictureUrl(): string
    {
        if (!$this->picture) {
            return '';
        }
        return '/uploads/' . HashedPackage::getSubdirectories($this->picture) . $this->picture;
    }

    /**
     * @param Role $role
     * @return User
     */
    public function setRole(Role $role)
    {
        $this->role = $role;
        return $this;
    }

    /** @return Role */
    public function getRole()
    {
        return $this->role;
    }

    /** @return PlayActivityEntry[]|ArrayCollection */
    public function getPlayActivityEntries()
    {
        return $this->playActivityEntries;
    }

    /** @return ArrayCollection|Game[] */
    public function getPlayedGames()
    {
        $playedGames = new ArrayCollection();
        foreach ($this->getPlayActivityEntries() as $activityEntry) {
            $playedGames->add($activityEntry->getGame());
        }
        return $playedGames;
    }

    /** @return bool */
    public function hasPlayActivityEntries(): bool
    {
        return $this->getPlayActivityEntries()->count() > 0;
    }

    /**
     * @param PlayActivityEntry $playActivityEntry
     * @return User
     */
    public function addPlayActivityEntries(PlayActivityEntry $playActivityEntry)
    {
        $this->playActivityEntries[] = $playActivityEntry;
        return $this;
    }

    public function addSocialNetworkConnection(SocialNetworkConnection $connection)
    {
        $this->socialNetworkConnections[] = $connection;
        return $this;
    }

    /** @return SocialNetworkConnection[]|ArrayCollection */
    public function getSocialNetworkConnections()
    {
        return $this->socialNetworkConnections;
    }

    /**
     * @param string $name
     * @return SocialNetworkConnection|null
     */
    public function getSocialNetworkConnection(string $name)
    {
        /** @var SocialNetworkConnection $connection */
        foreach ($this->socialNetworkConnections as $connection) {
            if ($connection->getName() === $name) {
                return $connection;
            }
        }
        return null;
    }

    /**
     * @param bool $emailConfirmed
     * @return User
     */
    public function setEmailConfirmed(bool $emailConfirmed)
    {
        $this->emailConfirmed = $emailConfirmed;
        return $this;
    }

    /** @return bool */
    public function isEmailConfirmed(): bool
    {
        return $this->emailConfirmed;
    }
}
