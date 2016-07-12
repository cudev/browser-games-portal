<?php

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
