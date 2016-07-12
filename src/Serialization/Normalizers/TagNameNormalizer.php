<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\Game\TagName;
use Ludos\Entity\Locale;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TagNameNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var TagName $object */
        return [
            'id' => $object->getId(),
            'slug' => $object->getSlug(),
            'translation' => $object->getTranslation(),
            'description' => $object->getDescription(),
            'locale' => $this->serializer->normalize($object->getLocale(), $format, $context)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof TagName;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var TagName $tagName */
        $tagName = $this->fetchEntity($data, TagName::class);
        $tagName->setSlug($data['slug'])
            ->setTranslation($data['translation'])
            ->setDescription($data['description'])
            ->setLocale($this->serializer->denormalize($data['locale'], Locale::class, $format, $context));
        return $tagName;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === TagName::class;
    }
}
