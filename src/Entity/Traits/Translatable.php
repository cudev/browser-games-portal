<?php

namespace Ludos\Entity\Traits;

use Ludos\Entity\Locale;

trait Translatable
{
    /**
     * @ManyToOne(targetEntity="\Ludos\Entity\Locale")
     * @JoinColumn(name="locale_id", referencedColumnName="id")
     */
    protected $locale;

    /** @Column(type="string") */
    protected $translation;

    /**
     * @param Locale $locale
     * @return $this
     */
    public function setLocale(Locale $locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * @return Locale|null
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string|null $translation
     * @return $this
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTranslation()
    {
        return $this->translation;
    }
}
