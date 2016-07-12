<?php

namespace Ludos\Entity;

use Ludos\Entity\Traits\Describable;
use Ludos\Entity\Traits\Identifiable;

/**
 * @Entity(repositoryClass="Ludos\Entity\Repositories\LocaleRepository")
 * @HasLifecycleCallbacks
 * @Table(name="locales")
 */
class Locale
{
    use Identifiable;
    use Describable;

    const DEFAULT_LANGUAGE = 'en';

    /** @Column(type="string") */
    protected $language;

    /** @Column(type="string") */
    protected $domain;

    /** @Column(type="string") */
    protected $title;

    /** @Column(type="string") */
    protected $contactEmail;

    /**
     * @param string $language
     * @return Locale
     */
    public function setLanguage(string $language)
    {
        $this->language = $language;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param string $domain
     * @return Locale
     */
    public function setDomain(string $domain)
    {
        $this->domain = $domain;
        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string|null $title
     * @return Locale
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string|null $contactEmail
     * @return Locale
     */
    public function setContactEmail($contactEmail)
    {
        $this->contactEmail = $contactEmail;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getContactEmail()
    {
        return $this->contactEmail;
    }
}
