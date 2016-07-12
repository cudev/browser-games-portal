<?php

use Abraham\TwitterOAuth\TwitterOAuth as TwitterClient;
use Facebook\Facebook as FacebookClient;
use Google_Client as GoogleClient;
use Ludos\Container\FacebookClientFactory;
use Ludos\Container\GoogleClientFactory;
use Ludos\Container\SocialSettingsAggregatorFactory;
use Ludos\Container\TwitterClientFactory;
use Ludos\Template\Aggregators\SocialSettingsAggregator;

return [
    'dependencies' => [
        'factories' => [
            FacebookClient::class => FacebookClientFactory::class,
            GoogleClient::class => GoogleClientFactory::class,
            TwitterClient::class => TwitterClientFactory::class,
            SocialSettingsAggregator::class => SocialSettingsAggregatorFactory::class
        ]
    ],
    'facebook' => [
        'app_id' => getenv('FACEBOOK_APP_ID'),
        'app_secret' => getenv('FACEBOOK_APP_SECRET'),
        'default_graph_version' => 'v2.5'
    ],
    'google' => [
        'client_id' => getenv('GOOGLE_CLIENT_ID'),
        'developer_key' => getenv('GOOGLE_DEVELOPER_KEY'),
        'client_secret' => getenv('GOOGLE_CLIENT_SECRET'),
        'analytics' => getenv('GOOGLE_ANALYTICS')
    ],
    'twitter' => [
        'consumer_key' => getenv('TWITTER_CONSUMER_KEY'),
        'consumer_secret' => getenv('TWITTER_CONSUMER_SECRET')
    ]
];
