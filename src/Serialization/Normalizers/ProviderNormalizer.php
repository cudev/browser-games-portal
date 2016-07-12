<?php

namespace Ludos\Serialization\Normalizers;

use Ludos\Entity\Provider\Provider;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProviderNormalizer extends EntityNormalizer implements NormalizerInterface, DenormalizerInterface
{

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = [])
    {
        /** @var Provider $object */
        return [
            'id' => $object->getId(),
            'name' => $object->getName()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Provider;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        /** @var Provider $provider */
        $provider = $this->fetchEntity($data, Provider::class);
        $provider->setName($data['name']);
        return $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === Provider::class;
    }

    protected function fetchEntity(array $data, string $entity)
    {
        $provider = null;
        if ($data['id'] !== null) {
            $provider = $this->entityManager->getRepository(Provider::class)->find($data['id']);
        }
        if ($provider === null && $data['name'] !== null) {
            $provider = $this->entityManager->getRepository(Provider::class)->findOneByName($data['name']);
        }
        if ($provider === null) {
            $provider = new Provider();
        }
        return $provider;
    }
}
