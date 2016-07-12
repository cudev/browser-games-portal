<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Interop\Container\ContainerInterface;

/** File is needed to run Doctrine CLI */

require realpath(__DIR__ . '/../vendor/autoload.php');
/** @var ContainerInterface $container */
$container = require 'container.php';

$entityManager = $container->get(EntityManager::class);

return ConsoleRunner::createHelperSet($entityManager);