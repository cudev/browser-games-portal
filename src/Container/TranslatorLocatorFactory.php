<?php

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
