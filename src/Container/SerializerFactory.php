<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016 Cudev Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights 
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell 
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

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
