<?php

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
