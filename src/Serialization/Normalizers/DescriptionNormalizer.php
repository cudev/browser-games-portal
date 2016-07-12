<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\Game\Description;
use Ludos\Entity\Locale;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DescriptionNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Description $object */
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
        return $data instanceof Description;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $Description = $this->fetchEntity($data, Description::class);
        $Description->setTranslation($data['translation'])
            ->setlocale($this->serializer->denormalize($data['locale'], Locale::class, $format, $context));
        return $Description;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Description::class;
    }
}
