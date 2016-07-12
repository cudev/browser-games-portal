<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\BannerTitle;
use Ludos\Entity\Locale;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BannerTitleNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var BannerTitle $object */
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
        return $data instanceof BannerTitle;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var BannerTitle $bannerTitle */
        $bannerTitle = $this->fetchEntity($data, BannerTitle::class);
        $bannerTitle->setTranslation($data['translation'])
            ->setLocale($this->serializer->denormalize($data['locale'], Locale::class, $format, $context));
        return $bannerTitle;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === BannerTitle::class;
    }
}
