<?php

use Ludos\Container\RendererFactory;
use Ludos\Template\Aggregators\AssetAggregator;
use Ludos\Template\Aggregators\AuthenticatedUserAggregator;
use Ludos\Template\Aggregators\LanguageSpecificAggregator;
use Ludos\Template\Aggregators\PlayedGamesAggregator;
use Ludos\Template\Aggregators\SocialSettingsAggregator;
use Ludos\Template\Aggregators\TagsAggregator;
use Zend\Expressive\Container\TemplatedErrorHandlerFactory;
use Zend\Expressive\Template\TemplateRendererInterface;
use Zend\Expressive\Twig\TwigRenderer;
use Zend\Expressive\Twig\TwigRendererFactory;

return [
    'dependencies' => [
        'factories' => [
            'Zend\Expressive\FinalHandler' => TemplatedErrorHandlerFactory::class,
            TwigRenderer::class => TwigRendererFactory::class,
            TemplateRendererInterface::class => RendererFactory::class
        ]
    ],

    'templates' => [
        'extension' => 'html.twig',
        'paths' => [
            'app' => ['resources/app/templates/app'],
            'initializers' => ['resources/app/templates/initializers'],
            'email' => ['resources/app/templates/email'],
            'dash' => ['resources/dash/templates/dash']
        ],
        'aggregators' => [
            AssetAggregator::class,
            TagsAggregator::class,
            LanguageSpecificAggregator::class,
            AuthenticatedUserAggregator::class,
            PlayedGamesAggregator::class,
            SocialSettingsAggregator::class
        ]
    ],

    'twig' => [
        'cache_dir' => 'storage/cache/twig',
        'assets_url' => '/',
        'assets_version' => null,
        'extensions' => [
            // extension service names or instances
        ],
    ],
];
