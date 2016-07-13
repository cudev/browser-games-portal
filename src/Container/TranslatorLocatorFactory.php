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

namespace Ludos\Container;

use Aura\Intl\FormatterLocator;
use Aura\Intl\IntlFormatter;
use Aura\Intl\Package;
use Aura\Intl\PackageLocator;
use Aura\Intl\TranslatorFactory;
use Aura\Intl\TranslatorLocator;
use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use Ludos\Entity\StaticContent;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;

class TranslatorLocatorFactory
{
    const DEFAULT_FORMATTER = 'intl';
    const TRANSLATION_CACHE_EXPIRATION = 3600 * 24 * 3; // 3 days

    public function __invoke(ContainerInterface $container)
    {
        $translatorLocator = new TranslatorLocator(
            new PackageLocator,
            new FormatterLocator([
                self::DEFAULT_FORMATTER => function () {
                    return new IntlFormatter;
                },
            ]),
            new TranslatorFactory,
            'en'
        );

        /** @var PackageLocator $packages */
        $packages = $translatorLocator->getPackages();
        foreach ($this->getTranslationMessages($container) as $language => $messages) {
            $packages->set(StaticContent::class, $language, function () use ($messages) {
                return new Package(self::DEFAULT_FORMATTER, null, $messages);
            });
        }

        return $translatorLocator;
    }

    /**
     * Retrieves translation messages from database or cache
     * @see StaticContent for more info
     * @param ContainerInterface $container
     * @return array
     * @throws ContainerException
     * @throws NotFoundException
     * @throws InvalidArgumentException
     */
    private function getTranslationMessages(ContainerInterface $container): array
    {
        /** @var CacheItemPoolInterface $cachePool */
        $cachePool = $container->get(CacheItemPoolInterface::class);

        $translationsCacheItem = $cachePool->getItem(StaticContent::class);
        $translations = $translationsCacheItem->get() ?? [];
        if (!$translationsCacheItem->isHit()) {
            /** @var EntityManager $entityManager */
            $entityManager = $container->get(EntityManager::class);
            $staticContents = $entityManager->getRepository(StaticContent::class)->findAll();

            $translations = [];
            /** @var StaticContent $staticContent */
            foreach ($staticContents as $staticContent) {
                foreach ($staticContent->getStaticContentData() as $staticContentData) {
                    $language = $staticContentData->getLocale()->getLanguage();
                    $translations[$language][$staticContent->getAccessKey()] = $staticContentData->getTranslation();
                }
            }

            $translationsCacheItem->set($translations);
            $translationsCacheItem->expiresAfter(self::TRANSLATION_CACHE_EXPIRATION);
            $cachePool->save($translationsCacheItem);
        }

        return $translations;
    }
}
