<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\Game\Meta;
use Ludos\Entity\Provider\Provider;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MetaNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Meta $object */
        $provider = $object->getProvider();
        return [
            'id' => $object->getId(),
            'data' => $object->getData(),
            'provider' => isset($provider) ? $this->serializer->normalize($provider, $format, $context) : null,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Meta;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var Meta $meta */
        $meta = $this->fetchEntity($data, Meta::class) ?? (new Meta())->setId($data['id']);
        $meta->setData($data['data']);
        if (isset($data['provider'])) {
            $meta->setProvider($this->serializer->denormalize($data['provider'], Provider::class, $format));
        }
        return $meta;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Meta::class;
    }
}
