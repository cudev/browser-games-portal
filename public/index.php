<?php

use Ludos\Schedule\TaskQueue;
use Ludos\TrackResponseTime;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Application;

// Delegate static file requests back to the PHP built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

chdir(dirname(__DIR__));
/** @noinspection UntrustedInclusionInspection */
require 'vendor/autoload.php';

/** @var \Interop\Container\ContainerInterface $container */
/** @noinspection UntrustedInclusionInspection */
$container = require 'config/container.php';

/** @var Application $app */
$app = $container->get(Application::class);
$request = $container->get(ServerRequestInterface::class);
$app->run($request);

fastcgi_finish_request();

$container->get(TrackResponseTime::class)();

/** @var TaskQueue $schedule */
$schedule = $container->get(TaskQueue::class);
// Process background tasks
$schedule->perform();