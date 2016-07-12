<?php

namespace Ludos\Container;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use Interop\Container\Exception\NotFoundException;
use ReflectionClass;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

class AutowiringFactory implements AbstractFactoryInterface
{

    /**
     * {@inheritdoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $reflectionClass = new ReflectionClass($requestedName);

        if (!$reflectionClass->isInstantiable()) {
            return false;
        }

        $constructor = $reflectionClass->getConstructor();
        if ($constructor === null) {
            return true;
        }

        foreach ($constructor->getParameters() as $parameter) {
            if (!$container->has($parameter->getClass()->getName())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     * @throws NotFoundException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $reflectionClass = new ReflectionClass($requestedName);
        $constructor = $reflectionClass->getConstructor();

        if ($constructor === null) {
            return $reflectionClass->newInstanceWithoutConstructor();
        }

        $parameters = [];
        foreach ($constructor->getParameters() as $parameter) {
            $parameterClass = $parameter->getClass()->getName();
            if (!$container->has($parameterClass)) {
                throw new ServiceNotCreatedException($parameterClass . ' not found.');
            }
            $parameters[] = $container->get($parameterClass);
        }

        return $reflectionClass->newInstanceArgs($parameters);
    }
}
