<?php

namespace Ludos\Serialization\Normalizers;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Serializer\Normalizer\SerializerAwareNormalizer;

class EntityNormalizer extends SerializerAwareNormalizer
{
    protected $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    protected function fetchEntity(array $data, string $entity)
    {
        return isset($data['id']) ? $this->entityManager->getRepository($entity)->find($data['id']) : new $entity;
    }
}
