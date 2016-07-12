<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\Locale;
use Ludos\Entity\StaticContentData;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class StaticContentDataNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var StaticContentData $object */
        return [
            'id' => $object->getId(),
            'translation' => $object->getTranslation(),
            'locale' => $this->serializer->normalize($object->getLocale(), $format, $context)
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof StaticContentData;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var StaticContentData $staticContentData */
        $staticContentData = $this->fetchEntity($data, StaticContentData::class);
        $staticContentData->setTranslation($data['translation'])
            ->setLocale($this->serializer->denormalize($data['locale'], Locale::class, $format, $context));
        return $staticContentData;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === StaticContentData::class;
    }
}
