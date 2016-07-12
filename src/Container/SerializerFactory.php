<?php

namespace Ludos\Container;

use Doctrine\ORM\EntityManager;
use Interop\Container\ContainerInterface;
use Ludos\Serialization\Normalizers\BannerNormalizer;
use Ludos\Serialization\Normalizers\BannerTitleNormalizer;
use Ludos\Serialization\Normalizers\CategoryNormalizer;
use Ludos\Serialization\Normalizers\DateTimeNormalizer;
use Ludos\Serialization\Normalizers\DescriptionNormalizer;
use Ludos\Serialization\Normalizers\EntityNormalizer;
use Ludos\Serialization\Normalizers\GameNormalizer;
use Ludos\Serialization\Normalizers\LocaleNormalizer;
use Ludos\Serialization\Normalizers\MetaNormalizer;
use Ludos\Serialization\Normalizers\ProviderNormalizer;
use Ludos\Serialization\Normalizers\StaticContentDataNormalizer;
use Ludos\Serialization\Normalizers\StaticContentNormalizer;
use Ludos\Serialization\Normalizers\TagNameNormalizer;
use Ludos\Serialization\Normalizers\TagNormalizer;
use Ludos\Serialization\Normalizers\UserNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Serializer;

class SerializerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $entityManager = $container->get(EntityManager::class);
        $normalizers = [
            new CategoryNormalizer($entityManager),
            new DateTimeNormalizer(),
            new DescriptionNormalizer($entityManager),
            new EntityNormalizer($entityManager),
            new GameNormalizer($entityManager),
            new LocaleNormalizer($entityManager),
            new MetaNormalizer($entityManager),
            new ProviderNormalizer($entityManager),
            new StaticContentNormalizer($entityManager),
            new StaticContentDataNormalizer($entityManager),
            new TagNameNormalizer($entityManager),
            new TagNormalizer($entityManager),
            new BannerNormalizer($entityManager),
            new BannerTitleNormalizer($entityManager),
            new UserNormalizer($entityManager)
        ];
        $encoders = [
            new JsonEncoder()
        ];
        return new Serializer($normalizers, $encoders);
    }
}
