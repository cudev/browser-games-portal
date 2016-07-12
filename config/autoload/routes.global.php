<?php

use Ludos\Action\Bookmark;
use Ludos\Action\Comments;
use Ludos\Action\Dashboard\Banners;
use Ludos\Action\Dashboard\Categories;
use Ludos\Action\Dashboard\CopyCategories;
use Ludos\Action\Dashboard\CrawlCategories;
use Ludos\Action\Dashboard\CrawlGames;
use Ludos\Action\Dashboard\Games;
use Ludos\Action\Dashboard\Locales;
use Ludos\Action\Dashboard\Providers;
use Ludos\Action\Dashboard\ShowIndexPage;
use Ludos\Action\Dashboard\StaticContent;
use Ludos\Action\Dashboard\Statistics;
use Ludos\Action\Dashboard\Tags;
use Ludos\Action\Dashboard\Users;
use Ludos\Action\DeleteUserPicture;
use Ludos\Action\EmailConfirm;
use Ludos\Action\Home;
use Ludos\Action\Rate;
use Ludos\Action\ShowAccount;
use Ludos\Action\ShowGame;
use Ludos\Action\ShowGames;
use Ludos\Action\ShowInfo;
use Ludos\Action\ShowLetter;
use Ludos\Action\SignIn;
use Ludos\Action\SignInFacebook;
use Ludos\Action\SignInFacebookCallback;
use Ludos\Action\SignInGoogle;
use Ludos\Action\SignInGoogleCallback;
use Ludos\Action\SignInTwitter;
use Ludos\Action\SignInTwitterCallback;
use Ludos\Action\SignOut;
use Ludos\Action\SignUp;
use Ludos\Action\Subscribe;
use Ludos\Action\UpdateAccountInfo;
use Ludos\Action\UpdateUserPicture;
use Ludos\Action\UserGames;
use Ludos\Container\AutowiringFactory;
use Ludos\Container\TemplateAggregatorMiddlewareFactory;
use Ludos\Middleware\AttachAssetPackage;
use Ludos\Middleware\AttachPlayedGames;
use Ludos\Middleware\AttachTranslator;
use Ludos\Middleware\AuthorizeAdmin;
use Ludos\Middleware\AuthorizeUser;
use Ludos\Middleware\TemplateAggregatorMiddleware;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\RouterInterface;

return [
    'dependencies' => [
        'invokables' => [
            RouterInterface::class => FastRouteRouter::class,
        ],
        'factories' => [
            TemplateAggregatorMiddleware::class => TemplateAggregatorMiddlewareFactory::class
        ],
        'abstract_factories' => [
            AutowiringFactory::class
        ]
    ],

    'routes' => [
        // Basic, visible routes
        [
            'name' => 'home',
            'path' => '/',
            'middleware' => [
                Home::class
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'user',
            'path' => '/user',
            'middleware' => [
                AuthorizeUser::class,
                ShowAccount::class
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'game.play',
            'path' => '/game/{gameSlug}',
            'middleware' => [
                ShowGame::class
            ],
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'tag',
            'path' => '/games[/{tagSlug}[/{order}[/{page}]]]',
            'middleware' => [
                ShowGames::class
            ],
            'allowed_methods' => ['GET']
        ],
        [
            'name' => 'letter',
            'path' => '/letter/{letterType}/{cryptedEmail}',
            'middleware' => [
                ShowLetter::class
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'email.confirm',
            'path' => '/email/confirm/{cryptedEmail}',
            'middleware' => [
                EmailConfirm::class
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'info',
            'path' => '/info/{staticPageName}',
            'middleware' => [
                ShowInfo::class
            ],
            'allowed_methods' => ['GET'],
        ],
        // System routes, async mostly
        [
            'name' => 'user.sign-in',
            'path' => '/user/sign-in',
            'middleware' => SignIn::class,
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'user.sign-in.facebook',
            'path' => '/user/sign-in/facebook',
            'middleware' => SignInFacebook::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'user.sign-in.facebook.callback',
            'path' => '/user/sign-in/facebook/callback',
            'middleware' => SignInFacebookCallback::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'user.sign-in.google',
            'path' => '/user/sign-in/google',
            'middleware' => SignInGoogle::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'user.sign-in.google.callback',
            'path' => '/user/sign-in/google/callback',
            'middleware' => SignInGoogleCallback::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'user.sign-in.twitter',
            'path' => '/user/sign-in/twitter',
            'middleware' => SignInTwitter::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'user.sign-in.twitter.callback',
            'path' => '/user/sign-in/twitter/callback',
            'middleware' => SignInTwitterCallback::class,
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'user.sign-up',
            'path' => '/user/sign-up',
            'middleware' => [
                SignUp::class
            ],
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'user.sign-out',
            'path' => '/user/sign-out',
            'middleware' => SignOut::class,
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'user.subscribe',
            'path' => '/user/subscribe',
            'middleware' => Subscribe::class,
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'user.update',
            'path' => '/user',
            'middleware' => [
                AuthorizeUser::class,
                UpdateAccountInfo::class
            ],
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'user.picture.delete',
            'path' => '/user/picture',
            'middleware' => [
                AuthorizeUser::class,
                DeleteUserPicture::class
            ],
            'allowed_methods' => ['DELETE'],
        ],
        [
            'name' => 'user.picture.update',
            'path' => '/user/picture',
            'middleware' => [
                AuthorizeUser::class,
                UpdateUserPicture::class
            ],
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'rate',
            'path' => '/rate',
            'middleware' => [
                AuthorizeUser::class,
                Rate::class
            ],
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'bookmark',
            'path' => '/bookmark',
            'middleware' => [
                AuthorizeUser::class,
                Bookmark::class
            ],
            'allowed_methods' => ['POST'],
        ],
        [
            'name' => 'game.comment',
            'path' => '/game/{gameSlug}/comment',
            'middleware' => [
                Comments::class
            ],
            'allowed_methods' => ['GET', 'POST', 'PATCH'],
        ],
        [
            'name' => 'user.games',
            'path' => '/user/games',
            'middleware' => [
                AuthorizeUser::class,
                UserGames::class
            ],
            'allowed_methods' => ['GET'],
        ],
        // Dashboard
        [
            'name' => 'dash.home',
            'path' => '/admin/',
            'middleware' => [
                AuthorizeAdmin::class,
                ShowIndexPage::class
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'api.games',
            'path' => '/api/games[/{id}]',
            'middleware' => [
                AuthorizeAdmin::class,
                Games::class
            ],
            'allowed_methods' => ['GET', 'PATCH', 'POST', 'DELETE'],
        ],
        [
            'name' => 'api.locales',
            'path' => '/api/locales[/{id}]',
            'middleware' => [
                AuthorizeAdmin::class,
                Locales::class
            ],
            'allowed_methods' => ['GET', 'PATCH', 'POST', 'DELETE'],
        ],
        [
            'name' => 'api.static-content',
            'path' => '/api/static-content[/{id}]',
            'middleware' => [
                AuthorizeAdmin::class,
                StaticContent::class
            ],
            'allowed_methods' => ['GET', 'PATCH', 'POST', 'DELETE', 'PUT'],
        ],
        [
            'name' => 'api.tags',
            'path' => '/api/tags[/{id}]',
            'middleware' => [
                AuthorizeAdmin::class,
                Tags::class
            ],
            'allowed_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
        ],
        [
            'name' => 'api.categories',
            'path' => '/api/categories[/{categoryId}]',
            'middleware' => [
                AuthorizeAdmin::class,
                Categories::class
            ],
            'allowed_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
        ],
        [
            'name' => 'api.banners',
            'path' => '/api/banners[/{bannerId}]',
            'middleware' => [
                AuthorizeAdmin::class,
                Banners::class
            ],
            'allowed_methods' => ['GET', 'POST', 'PATCH', 'DELETE'],
        ],
        [
            'name' => 'api.providers.categories.crawl',
            'path' => '/api/providers/{providerId}/categories/crawl',
            'middleware' => [
                AuthorizeAdmin::class,
                CrawlCategories::class
            ],
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'api.providers.categories.copy',
            'path' => '/api/providers/{providerId}/categories/copy',
            'middleware' => [
                AuthorizeAdmin::class,
                CopyCategories::class
            ],
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'api.providers.games.crawl',
            'path' => '/api/providers/{providerId}/games/crawl',
            'middleware' => [
                AuthorizeAdmin::class,
                CrawlGames::class
            ],
            'allowed_methods' => ['GET', 'POST'],
        ],
        [
            'name' => 'api.providers.categories',
            'path' => '/api/providers/{providerId}/categories',
            'middleware' => [
                AuthorizeAdmin::class,
                Categories::class
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'api.providers',
            'path' => '/api/providers/{id}',
            'middleware' => [
                AuthorizeAdmin::class,
                Providers::class
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'api.stats',
            'path' => '/api/stats',
            'middleware' => [
                AuthorizeAdmin::class,
                Statistics::class
            ],
            'allowed_methods' => ['GET'],
        ],
        [
            'name' => 'api.users',
            'path' => '/api/users',
            'middleware' => [
                AuthorizeAdmin::class,
                Users::class
            ],
            'allowed_methods' => ['GET'],
        ]
    ],
];
