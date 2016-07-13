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

use Doctrine\Common\Cache\RedisCache;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\Tools\Setup;
use Interop\Container\ContainerInterface;
use Redis;

class EntityManagerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $doctrineConfiguration = $this->makeDoctrineConfiguration($container);
        return EntityManager::create($config['doctrine']['connection'], $doctrineConfiguration);
    }

    private function makeRedisCache(ContainerInterface $container): RedisCache
    {
        $redis = new Redis();
        $redis->connect($container->get('config')['redis']['connection']['host']);
        $redisCache = new RedisCache();
        $redisCache->setRedis($redis);
        return $redisCache;
    }

    private function makeDoctrineConfiguration(ContainerInterface $container): Configuration
    {
        $config = $container->get('config');
        $isDevMode = $config['debug'];
        $cache = null;
        if (!$isDevMode) {
            $cache = $this->makeRedisCache($container);
        }

        $doctrineConfiguration = Setup::createAnnotationMetadataConfiguration(
            $config['doctrine']['paths']['entities'],
            $config['debug'],
            $config['doctrine']['paths']['proxy'],
            $cache
        );
        $doctrineConfiguration->setNamingStrategy(new UnderscoreNamingStrategy(CASE_LOWER));
        return $doctrineConfiguration;
    }
}
