<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\Locale;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class LocaleNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Locale $object */
        return [
            'id' => $object->getId(),
            'language' => $object->getLanguage(),
            'domain' => $object->getDomain(),
            'title' => $object->getTitle(),
            'description' => $object->getDescription(),
            'contactEmail' => $object->getContactEmail()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Locale;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var Locale $locale */
        $locale = $this->fetchEntity($data, Locale::class);
        $locale->setDomain($data['domain'])
            ->setLanguage($data['language'])
            ->setDescription($data['description'] ?? null)
            ->setContactEmail($data['contactEmail'] ?? null)
            ->setTitle($data['title'] ?? null);
        return $locale;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Locale::class;
    }
}
