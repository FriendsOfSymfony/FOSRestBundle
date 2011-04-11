<?php

namespace FOS\RestBundle\Routing\Loader;

use Doctrine\Common\Annotations\AnnotationReader;
require_once __DIR__.'/../../Controller/Annotations.php';

use Symfony\Component\Config\Loader\LoaderInterface,
    Symfony\Component\Config\Loader\LoaderResolver,
    Symfony\Component\Config\Resource\FileResource,
    Symfony\Component\Routing\Route,
    Symfony\Bundle\FrameworkBundle\Controller\ControllerNameParser;

use FOS\RestBundle\Routing\RestRouteCollection,
    FOS\RestBundle\Pluralization\Pluralization;

/*
 * This file is part of the FOS/RestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * RestRouteLoader REST-enabled controller router loader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class RestRouteLoader implements LoaderInterface
{
    protected $availableHTTPMethods;
    protected $annotationClasses;
    protected $parents = array();
    protected $prefix;
    protected $namePrefix;

    /**
     * Holds AnnotationReader instance
     *
     * @var Doctrine\Common\Annotations\AnnotationReader
     */
    private $reader;

    /**
     * Initialize REST Controller routes loader.
     *
     * @param   AnnotationReader        $reader     annotations reader
     */
    public function __construct(AnnotationReader $reader)
    {
        $this->reader               = $reader;
        $this->availableHTTPMethods = array('get', 'post', 'put', 'delete', 'head');
        $this->annotationClasses    = array(
            'FOS\RestBundle\Controller\Annotations\Route',
            'FOS\RestBundle\Controller\Annotations\Get',
            'FOS\RestBundle\Controller\Annotations\Post',
            'FOS\RestBundle\Controller\Annotations\Put',
            'FOS\RestBundle\Controller\Annotations\Delete',
            'FOS\RestBundle\Controller\Annotations\Head'
        );
    }

    /**
     * Set parent routes.
     *
     * @param   array   $parents    Array of parent resources names
     */
    public function setParents(array $parents)
    {
        $this->parents = $parents;
    }

    /**
     * Set routes prefix.
     *
     * @param   string  $prefix     Routes prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Set route names prefix.
     *
     * @param   string  $namePrefix Route names prefix
     */
    public function setRouteNamesPrefix($namePrefix)
    {
        $this->namePrefix = $namePrefix;
    }

    /**
     * Loads a Routes collection by parsing Controller method names.
     *
     * @param   string  $class      A controller class
     * @param   string  $type       The resource type
     *
     * @return  RouteCollection     A RouteCollection instance
     */
    public function load($class, $type = null)
    {
        // Check that class exists
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        // Check that every passed parent has non-empty singular name
        foreach ($this->parents as $parent) {
            if (empty($parent) || '/' === mb_substr($parent, -1)) {
                throw new \InvalidArgumentException('All parent controllers must have ::getSINGULAR_NAME() action');
            }
        }

        // Trim "/" at the start
        if (null !== $this->prefix && isset($this->prefix[0]) && '/' === $this->prefix[0]) {
            $this->prefix = mb_substr($this->prefix, 1);
        }

        $class      = new \ReflectionClass($class);
        $collection = new RestRouteCollection();
        $collection->addResource(new FileResource($class->getFileName()));
        $routeAnnotationClass = 'FOS\RestBundle\Controller\Annotations\Route';

        $patternStartRoute = $this->reader->getClassAnnotation($class, $routeAnnotationClass);
        $patternStart = null;
        if($patternStartRoute)
        {
            $patternStart = trim($patternStartRoute->getPattern(),"/");
        }
        
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $matches = array();

            // If method name starts with underscore - skip
            if ('_' === mb_substr($method->getName(), 0, 1)) {
                continue;
            }

            // If method has @rest:NoRoute annotation - skip
            $noAnnotationClass = 'FOS\RestBundle\Controller\Annotations\NoRoute';
            if (null !== $this->reader->getMethodAnnotation($method, $noAnnotationClass)) {
                continue;
            }

            if (preg_match('/([a-z][_a-z0-9]+)(.*)Action/', $method->getName(), $matches)) {
                $httpMethod = $matches[1];
                $resources  = preg_split('/([A-Z][^A-Z]*)/', $matches[2], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                $arguments  = $method->getParameters();

                // If we have 1 resource passed & 1 argument, then it's object call, so
                // we can set collection singular name
                if (1 === count($resources) && 1 === count($arguments) - count($this->parents)) {
                    $collection->setSingularName($resources[0]);
                }

                // If we have parents passed - merge them with own resource names
                if (count($this->parents)) {
                    $resources = array_merge($this->parents, $resources);
                }

                $urlParts   = array();
                $routeName  = $httpMethod;
                foreach ($resources as $i => $resource) {
                    $routeName .= '_' . basename($resource);

                    // If we already added all parent routes paths to URL & we have prefix - add it
                    if (!empty($this->prefix) && $i === count($this->parents)) {
                        $urlParts[] = $this->prefix;
                    }

                    // If we have argument for current resource, then it's object. Otherwise - it's collection
                    if (isset($arguments[$i])) {
                        if($patternStart) {
                            $urlParts[] = $patternStart . '/{' . $arguments[$i]->getName() . '}';
                        } else {
                            $urlParts[] =
                                Pluralization::pluralize($resource) . '/{' . $arguments[$i]->getName() . '}';
                        }
                    } else {
                        if($patternStart) {
                            $urlParts[] = $patternStart;
                        } else {
                            $urlParts[] = $resource;
                        }
                    }
                }

                // If passed method is not valid HTTP method, then it's custom object (PUT) or collection (GET) method
                if (!in_array($httpMethod, $this->availableHTTPMethods)) {
                    $urlParts[] = $httpMethod;
                    $httpMethod = 'put';

                    if (count($arguments) < count($resources)) {
                        $httpMethod = 'get';
                    }
                }
                
                $pattern        = mb_strtolower(implode('/', $urlParts));
                $defaults       = array('_controller' => $class->getName() . '::' . $method->getName());
                $requirements   = array('_method'     => mb_strtoupper($httpMethod));
                $options        = array();
                
                // Read annotations
                foreach ($this->annotationClasses as $annotationClass) {
                    $routeAnnnotation = $this->reader->getMethodAnnotation($method, $annotationClass);

                    if (null !== $routeAnnnotation) {
                        $annoRequirements   = $routeAnnnotation->getRequirements();

                        if (!isset($annoRequirements['_method']) || null === $annoRequirements['_method']) {
                            $annoRequirements['_method'] = $requirements['_method'];
                        }

                        $pattern        = $routeAnnnotation->getPattern() ?: $pattern;
                        $requirements   = array_merge($requirements, $annoRequirements);
                        $options        = array_merge($options, $routeAnnnotation->getOptions());
                        $defaults       = array_merge($defaults, $routeAnnnotation->getDefaults());

                        break;
                    }
                }

                // Create route with gathered parameters
                $route = new Route($pattern, $defaults, $requirements, $options);

                $collection->add($this->namePrefix . mb_strtolower($routeName), $route);
            }
        }

        return $collection;
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param  mixed  $resource A resource
     * @param  string $type     The resource type
     *
     * @return Boolean true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) &&
            preg_match('/^(?:\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+$/', $resource) &&
            'rest' === $type;
    }

    /**
     * Gets the loader resolver.
     *
     * @return LoaderResolver A LoaderResolver instance
     */
    public function getResolver() {}

    /**
     * Sets the loader resolver.
     *
     * @param LoaderResolver $resolver A LoaderResolver instance
     */
    public function setResolver(LoaderResolver $resolver) {}
}
