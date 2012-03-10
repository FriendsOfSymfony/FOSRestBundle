<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Routing\Loader;

use Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\Config\Loader\LoaderInterface,
    Symfony\Component\Config\Loader\LoaderResolver,
    Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Routing\Loader\Reader\RestControllerReader;

/**
 * RestRouteLoader REST-enabled controller router loader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class RestRouteLoader implements LoaderInterface
{
    private $container;
    private $controllerParser;
    private $controllerReader;
    private $defaultFormat;

    /**
     * Initializes loader.
     *
     * @param ContainerInterface   $container        service container
     * @param ControllerNameParser $controllerParser controller name parser
     * @param RestControllerReader $controllerReader controller reader
     * @param string               $defaultFormat    default http format
     */
    public function __construct(ContainerInterface $container,
                                ControllerNameParser $controllerParser,
                                RestControllerReader $controllerReader, $defaultFormat = 'html')
    {
        $this->container        = $container;
        $this->controllerParser = $controllerParser;
        $this->controllerReader = $controllerReader;
        $this->defaultFormat    = $defaultFormat;
    }

    /**
     * Returns controller reader.
     *
     * @return RestControllerReader
     */
    public function getControllerReader()
    {
        return $this->controllerReader;
    }

    /**
     * Loads a Routes collection by parsing Controller method names.
     *
     * @param string $controller Some identifier for the controller
     * @param string $type       The resource type
     *
     * @return RouteCollection A RouteCollection instance
     */
    public function load($controller, $type = null)
    {
        list($prefix, $class) = $this->getControllerLocator($controller);

        $collection = $this->controllerReader->read(new \ReflectionClass($class));
        $collection->prependRouteControllersWithPrefix($prefix);
        $collection->setDefaultFormat($this->defaultFormat);

        return $collection;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource)
            && 'rest' === $type
            && !in_array(pathinfo($resource, PATHINFO_EXTENSION), array('xml', 'yml'));
    }

    /**
     * Gets the loader resolver.
     *
     * @return LoaderResolverInterface A LoaderResolver instance
     */
    public function getResolver()
    {
    }

    /**
     * Sets the loader resolver.
     *
     * @param LoaderResolverInterface $resolver A LoaderResolver instance
     */
    public function setResolver(LoaderResolver $resolver)
    {
    }

    /**
     * Returns controller locator by it's id.
     *
     * @param string $controller
     *
     * @return array
     */
    private function getControllerLocator($controller)
    {
        $class  = null;
        $prefix = null;

        if (class_exists($controller)) {
            // full class name
            $class  = $controller;
            $prefix = $class . '::';
        } elseif (false !== strpos($controller, ':')) {
            // bundle:controller notation
            try {
                $notation             = $this->controllerParser->parse($controller . ':method');
                list($class, $method) = explode('::', $notation);
                $prefix               = $class . '::';
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    sprintf('Can\'t locate "%s" controller.', $controller)
                );
            }
        } elseif ($this->container->has($controller)) {
            // service_id
            $prefix = $controller . ':';
            $this->container->enterScope('request');
            $this->container->set('request', new Request);
            $class = get_class($this->container->get($controller));
            $this->container->leaveScope('request');
        }

        if (empty($class)) {
            throw new \InvalidArgumentException(sprintf(
                'Class could not be determined for Controller identified by "%s".', $controller
            ));
        }

        return array($prefix, $class);
    }
}
