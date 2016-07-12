<?php

use Ludos\Container\DetectDeviceFactory;
use Ludos\Container\AggregateTemplateVariablesFactory;
use Ludos\Container\TemplateAggregatorMiddlewareFactory;
use Ludos\Middleware\Authentication;
use Ludos\Middleware\DetectSupportedGames;
use Ludos\Middleware\NotFound;
use Ludos\Middleware\AggregateTemplateVariables;
use Ludos\Middleware\SetupLocale;
use Ludos\Middleware\ShowError;
use Ludos\Middleware\TemplateAggregatorMiddleware;
use Ludos\Middleware\TrackResponseTime;
use Zend\Expressive\Container\ApplicationFactory;
use Zend\Expressive\Helper\ServerUrlMiddleware;
use Zend\Expressive\Helper\ServerUrlMiddlewareFactory;

return [
    'dependencies' => [
        'factories' => [
            ServerUrlMiddleware::class => ServerUrlMiddlewareFactory::class,
//            UrlHelperMiddleware::class => UrlHelperMiddlewareFactory::class,
            DetectSupportedGames::class => DetectDeviceFactory::class
        ],
    ],
    'middleware_pipeline' => [

        // An array of middleware to pipe to the application.
        // Each item is of the following structure:
        // [
        //     // Required:
        //     'middleware' => 'Name or array of names of middleware services and/or callables',
        //     // Optional:
        //     'path'  => '/path/to/match',
        //     'error' => true,
        // ],
        [
            'middleware' => [
                ServerUrlMiddleware::class,
//                UrlHelperMiddleware::class,
                DetectSupportedGames::class,
                SetupLocale::class,
                Authentication::class,
                TemplateAggregatorMiddleware::class
//                CudevAccessOnly::class,
            ],
            'priority' => PHP_INT_MAX,
        ],

        // The following is an entry for:
        // - routing middleware
        // - middleware that reacts to the routing results
        // - dispatch middleware
        [
            'middleware' => [
                ApplicationFactory::ROUTING_MIDDLEWARE,
//                UrlHelperMiddleware::class,
                ApplicationFactory::DISPATCH_MIDDLEWARE,
            ],
            'priority' => 1,
        ],
        [
            'middleware' => [
                NotFound::class
            ],
            'priority' => -1,
        ],

        // The following is an entry for the dispatch middleware:

        // Place error handling middleware after the routing and dispatch
        // middleware, with negative priority.
        [
            'middleware' => [
                ShowError::class
            ],
            'error' => true,
            'priority' => -1000,
        ],
    ],
];
