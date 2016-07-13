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

namespace Ludos\Template\Aggregators;

use Aura\Intl\TranslatorInterface;
use Aura\Intl\TranslatorLocator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Ludos\Entity\Locale;
use Ludos\Entity\StaticContent;
use Ludos\Template\AbstractAggregator;
use Psr\Http\Message\ServerRequestInterface;

class LanguageSpecificAggregator extends AbstractAggregator
{
    private $translatorLocator;
    private $entityManager;

    public function __construct(TranslatorLocator $translatorLocator, EntityManager $entityManager)
    {
        $this->translatorLocator = $translatorLocator;
        $this->templateNames[] = 'email::confirmation';
        $this->templateNames[] = 'email::notification';
        $this->entityManager = $entityManager;
    }

    public function getTemplateVariables(): array
    {
        $locale = $this->request->getAttribute(Locale::class);
        $translator = $this->translatorLocator->get(StaticContent::class, $locale->getLanguage());

        $locales = $this->entityManager->getRepository(Locale::class)->findAll();
        $domains = (new ArrayCollection($locales))->map(function (Locale $locale) {
            return $locale->getDomain();
        });

        return [
            'translator' => $translator,
            'locale' => $locale,
            'domains' => $domains->toArray(),
            'translations' => $this->extractFrontEndTranslations($translator),
            'canonical' => (string)$this->request->getUri()
        ];
    }

    private function extractFrontEndTranslations(TranslatorInterface $translator): array
    {
        $accessKeys = [
            'comment.create',
            'comment.placeholder',
            'comment.unauthorized',
            'sign.up',
            'sign.in',
            'games.recommended',
            'games.my.bookmarked',
            'games.my.last',
            'account.age',
            'account.name',
            'account.email',
            'account.birthday',
            'account.gender',
            'account.gender.male',
            'account.gender.female',
            'account.picture.upload',
            'account.picture.remove',
            'account.save',
            'account.edit',
            'games.my.bookmarked.empty',
            'games.my.last.empty',
            'account.picture.upload.large',
            'account.name.error.length',
            'footer.newsletter.subscribe.placeholder',
            'footer.newsletter.subscribed',
            'footer.newsletter.error',
            'footer.newsletter.subscribe',
        ];
        $translations = [];
        foreach ($accessKeys as $accessKey) {
            $translations[$accessKey] = $translator->translate($accessKey);
        }
        return $translations;
    }
}
