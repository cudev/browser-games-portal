<?php

namespace Ludos\Container;

use Interop\Container\ContainerInterface;
use Ludos\Entity\Locale;
use Psr\Http\Message\ServerRequestInterface;
use SplPriorityQueue;
use Zend\Diactoros\Response\EmitterInterface;
use Zend\Expressive\Application;
use Zend\Expressive\Container\ApplicationFactory as ZendApplicationFactory;
use Zend\Expressive\Container\Exception\InvalidArgumentException as ContainerInvalidArgumentException;
use Zend\Expressive\Exception;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\Route;
use Zend\Expressive\Router\RouterInterface;

/**
 * =============================================================================
 * ATTENTION: MODIFIED COPYPASTE FROM ZEND EXPRESSIVE PACKAGE HERE!
 * @see \Zend\Expressive\Container\ApplicationFactory
 * =============================================================================
 */
class ApplicationFactory extends ZendApplicationFactory
{

    /**
     * Create and return an Application instance.
     *
     * See the class level docblock for information on what services this
     * factory will optionally consume.
     *
     * @param ContainerInterface $container
     * @return Application
     * @throws \Zend\Expressive\Router\Exception\InvalidArgumentException
     * @throws \Zend\Expressive\Exception\InvalidMiddlewareException
     * @throws \Zend\Expressive\Container\Exception\InvalidArgumentException
     * @throws \Interop\Container\Exception\NotFoundException
     * @throws \Zend\Expressive\Exception\InvalidArgumentException
     * @throws \Interop\Container\Exception\ContainerException
     */
    public function __invoke(ContainerInterface $container)
    {
        $router = $container->has(RouterInterface::class)
            ? $container->get(RouterInterface::class)
            : new FastRouteRouter();

        $finalHandler = $container->has('Zend\Expressive\FinalHandler')
            ? $container->get('Zend\Expressive\FinalHandler')
            : null;

        $emitter = $container->has(EmitterInterface::class)
            ? $container->get(EmitterInterface::class)
            : null;

        $app = new Application($router, $container, $finalHandler, $emitter);

        $this->injectRoutesAndPipeline($app, $container);

        return $app;
    }

    /**
     * Injects routes and the middleware pipeline into the application.
     *
     * @param Application $app
     * @param ContainerInterface $container
     * @throws \Interop\Container\Exception\ContainerException
     * @throws \Zend\Expressive\Exception\InvalidArgumentException
     * @throws \Zend\Expressive\Container\Exception\InvalidArgumentException
     * @throws \Zend\Expressive\Router\Exception\InvalidArgumentException
     * @throws \Zend\Expressive\Exception\InvalidMiddlewareException
     * @throws \Interop\Container\Exception\NotFoundException
     */
    private function injectRoutesAndPipeline(Application $app, ContainerInterface $container)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $pipelineCreated = false;

        if (array_key_exists('middleware_pipeline', $config) && is_array($config['middleware_pipeline'])) {
            $pipelineCreated = $this->injectMiddleware($config['middleware_pipeline'], $app);
        }

        $language = $container->get(ServerRequestInterface::class)->getAttribute(Locale::class)->getLanguage();

        if (array_key_exists('routes', $config) && is_array($config['routes'])) {
            $this->injectRoutes($config['routes'], $app, $language);

            if (!$pipelineCreated) {
                $app->pipeRoutingMiddleware();
                $app->pipeDispatchMiddleware();
            }
        }
    }

    /**
     * Inject routes from configuration, if any.
     *
     * @param array $routes Route definitions
     * @param Application $app
     * @throws \Zend\Expressive\Container\Exception\InvalidArgumentException
     * @throws \Zend\Expressive\Exception\InvalidArgumentException
     * @throws \Zend\Expressive\Router\Exception\InvalidArgumentException
     */
    private function injectRoutes(array $routes, Application $app, $language)
    {
        foreach ($routes as $spec) {
            if (!isset($spec['path'], $spec['middleware'])) {
                continue;
            }

            if (isset($spec['allowed_methods'])) {
                $methods = $spec['allowed_methods'];
                if (!is_array($methods)) {
                    throw new ContainerInvalidArgumentException(sprintf(
                        'Allowed HTTP methods for a route must be in form of an array; received "%s"',
                        gettype($methods)
                    ));
                }
            } else {
                $methods = Route::HTTP_METHOD_ANY;
            }
            $name = $spec['name'] ?? null;

            if (array_key_exists('translations', $spec) && array_key_exists($language, $spec['translations'])) {
                $path = $spec['translations'][$language];
            } else {
                $path = $spec['path'];
            }

            $route = new Route($path, $spec['middleware'], $methods, $name);

            if (isset($spec['options'])) {
                $options = $spec['options'];
                if (!is_array($options)) {
                    throw new ContainerInvalidArgumentException(sprintf(
                        'Route options must be an array; received "%s"',
                        gettype($options)
                    ));
                }

                $route->setOptions($options);
            }

            $app->route($route);
        }
    }

    /**
     * Given a collection of middleware specifications, pipe them to the application.
     *
     * @param array $collection
     * @param Application $app
     * @return bool Flag indicating whether or not any middleware was injected.
     * @throws \Zend\Expressive\Container\Exception\InvalidArgumentException
     * @throws Exception\InvalidMiddlewareException for invalid middleware.
     */
    private function injectMiddleware(array $collection, Application $app)
    {
        // Create a priority queue from the specifications
        $queue = array_reduce(
            array_map($this->createCollectionMapper($app), $collection),
            $this->createPriorityQueueReducer(),
            new SplPriorityQueue()
        );

        $injections = count($queue) > 0;

        foreach ($queue as $spec) {
            $path = $spec['path'] ?? '/';
            $error = array_key_exists('error', $spec) ? (bool)$spec['error'] : false;
            $pipe = $error ? 'pipeErrorHandler' : 'pipe';

            $app->{$pipe}($path, $spec['middleware']);
        }

        return $injections;
    }

    /**
     * Create and return the pipeline map callback.
     *
     * The returned callback has the signature:
     *
     * <code>
     * function ($item) : callable|string
     * </code>
     *
     * It is suitable for mapping pipeline middleware representing the application
     * routing o dispatching middleware to a callable; if the provided item does not
     * match either, the item is returned verbatim.
     *
     * @param Application $app
     * @return callable
     */
    private function createPipelineMapper(Application $app)
    {
        return function ($item) use ($app) {
            if ($item === self::ROUTING_MIDDLEWARE) {
                return [$app, 'routeMiddleware'];
            }

            if ($item === self::DISPATCH_MIDDLEWARE) {
                return [$app, 'dispatchMiddleware'];
            }

            return $item;
        };
    }

    /**
     * Create the collection mapping function.
     *
     * Returns a callable with the following signature:
     *
     * <code>
     * function (array|string $item) : array
     * </code>
     *
     * When it encounters one of the self::*_MIDDLEWARE constants, it passes
     * the value to the `createPipelineMapper()` callback to create a spec
     * that uses the return value as pipeline middleware.
     *
     * If the 'middleware' value is an array, it uses the `createPipelineMapper()`
     * callback as an array mapper in order to ensure the self::*_MIDDLEWARE
     * are injected correctly.
     *
     * If the 'middleware' value is missing, or not viable as middleware, it
     * raises an exception, to ensure the pipeline is built correctly.
     *
     * @param Application $app
     * @return callable
     * @throws \Zend\Expressive\Container\Exception\InvalidArgumentException
     */
    private function createCollectionMapper(Application $app)
    {
        $pipelineMap = $this->createPipelineMapper($app);
        $appMiddlewares = [
            self::ROUTING_MIDDLEWARE,
            self::DISPATCH_MIDDLEWARE
        ];

        return function ($item) use ($app, $pipelineMap, $appMiddlewares) {
            if (in_array($item, $appMiddlewares, true)) {
                return ['middleware' => $pipelineMap($item)];
            }

            if (!is_array($item) || !array_key_exists('middleware', $item)) {
                throw new ContainerInvalidArgumentException(sprintf(
                    'Invalid pipeline specification received; must be an array containing a middleware '
                    . 'key, or one of the ApplicationFactory::*_MIDDLEWARE constants; received %s',
                    (is_object($item) ? get_class($item) : gettype($item))
                ));
            }

            if (is_array($item['middleware']) && !is_callable($item['middleware'])) {
                $item['middleware'] = array_map($pipelineMap, $item['middleware']);
            }

            return $item;
        };
    }

    /**
     * Create reducer function that will reduce an array to a priority queue.
     *
     * Creates and returns a function with the signature:
     *
     * <code>
     * function (SplQueue $queue, array $item) : SplQueue
     * </code>
     *
     * The function is useful to reduce an array of pipeline middleware to a
     * priority queue.
     *
     * @return callable
     */
    private function createPriorityQueueReducer()
    {
        // $serial is used to ensure that items of the same priority are enqueued
        // in the order in which they are inserted.
        $serial = PHP_INT_MAX;
        return function ($queue, $item) use (&$serial) {
            $priority = array_key_exists('priority', $item) && is_int($item['priority'])
                ? $item['priority']
                : 1;
            $queue->insert($item, [$priority, $serial--]);
            return $queue;
        };
    }
}
