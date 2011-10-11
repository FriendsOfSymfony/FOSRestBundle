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
    Symfony\Component\Config\Resource\FileResource,
    Symfony\Component\Routing\Route,
    Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\Routing\RestRouteCollection,
    FOS\RestBundle\Util\Pluralization;

use Doctrine\Common\Annotations\Reader;

/**
 * RestRouteLoader REST-enabled controller router loader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class RestRouteLoader implements LoaderInterface
{
    protected $container;
    protected $parser;
    protected $availableHTTPMethods;
    protected $availableConventionalActions;
    protected $annotationClasses;
    protected $parents = array();
    protected $prefix;
    protected $namePrefix;
    protected $defaultFormat;

    /**
     * Holds AnnotationReader instance
     *
     * @var Doctrine\Common\Annotations\AnnotationReader
     */
    private $reader;

    /**
     * Initialize REST Controller routes loader.
     *
     * @param   ContainerInterface      $container     service container
     * @param   ControllerNameParser    $parser        controller name parser
     * @param   Reader                  $reader        annotations reader
     * @param   string                  $defaultFormat default route format
     */
    public function __construct(ContainerInterface $container, ControllerNameParser $parser, Reader $reader, $defaultFormat)
    {
        $this->container            = $container;
        $this->parser               = $parser;
        $this->reader               = $reader;
        $this->defaultFormat        = $defaultFormat;
        $this->availableHTTPMethods = array('get', 'post', 'put', 'patch', 'delete', 'head');
        $this->availableConventionalActions = array('new', 'edit', 'remove');
        $this->annotationClasses    = array(
            'FOS\RestBundle\Controller\Annotations\Route',
            'FOS\RestBundle\Controller\Annotations\Get',
            'FOS\RestBundle\Controller\Annotations\Post',
            'FOS\RestBundle\Controller\Annotations\Put',
            'FOS\RestBundle\Controller\Annotations\Patch',
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
     * @param   string  $controller Some identifier for the controller
     * @param   string  $type       The resource type
     *
     * @return  RouteCollection     A RouteCollection instance
     */
    public function load($controller, $type = null)
    {
        if (class_exists($controller)) {
            // full class name
            $class            = $controller;
            $controllerPrefix = $class . '::';
        } elseif (false !== strpos($controller, ':')) {
            // bundle:controller notation
            try {
                $notation             = $this->parser->parse($controller . ':method');
                list($class, $method) = explode('::', $notation);
                $controllerPrefix     = $class . '::';
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(sprintf('Can\'t locate "%s" controller.', $controller));
            }
        } elseif ($this->container->has($controller)) {
            // service_id
            $controllerPrefix = $controller . ':';
            // FIXME: this is ugly, but I do not see any good alternative
            $this->container->enterScope('request');
            $this->container->set('request', new Request);
            $class = get_class($this->container->get($controller));
            $this->container->leaveScope('request');
        }

        if (empty($class)) {
            throw new \InvalidArgumentException(sprintf('Class could not be determined for Controller identified by "%s".', $controller));
        }

        // Check that every passed parent has non-empty singular name
        foreach ($this->parents as $parent) {
            if (empty($parent) || '/' === substr($parent, -1)) {
                throw new \InvalidArgumentException('All parent controllers must have ::getSINGULAR_NAME() action');
            }
        }

        $class      = new \ReflectionClass($class);
        $collection = new RestRouteCollection();
        $collection->addResource(new FileResource($class->getFileName()));

        $prefixAnnotationClass = 'FOS\RestBundle\Controller\Annotations\Prefix';
        $prefix = $this->reader->getClassAnnotation($class, $prefixAnnotationClass);
        if ($prefix) {
            $this->prefix = $prefix->value;
        }

        $namePrefixAnnotationClass = 'FOS\RestBundle\Controller\Annotations\NamePrefix';
        $namePrefix = $this->reader->getClassAnnotation($class, $namePrefixAnnotationClass);
        if ($namePrefix) {
            $this->namePrefix = $namePrefix->value;
        }

        // Trim "/" at the start
        if (null !== $this->prefix && isset($this->prefix[0]) && '/' === $this->prefix[0]) {
            $this->prefix = substr($this->prefix, 1);
        }

        $routeAnnotationClass = 'FOS\RestBundle\Controller\Annotations\Route';

        $patternStartRoute = $this->reader->getClassAnnotation($class, $routeAnnotationClass);
        $patternStart = null;

        if ($patternStartRoute) {
            $patternStart = trim($patternStartRoute->getPattern(), "/");
        }

        $routes = array();
        foreach ($class->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $matches = array();

            // If method name starts with underscore - skip
            if ('_' === substr($method->getName(), 0, 1)) {
                continue;
            }

            // If method has @NoRoute annotation - skip
            $noAnnotationClass = 'FOS\RestBundle\Controller\Annotations\NoRoute';
            if (null !== $this->reader->getMethodAnnotation($method, $noAnnotationClass)) {
                continue;
            }

            if (preg_match('/([a-z][_a-z0-9]+)(.*)Action/', $method->getName(), $matches)) {
                $httpMethod = $matches[1];
                $resources  = preg_split('/([A-Z][^A-Z]*)/', $matches[2], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
                $arguments  = $method->getParameters();

                // Ignore arguments that are or extend from Symfony\Component\HttpFoundation\Request
                foreach ($arguments as $key => $argument) {
                    $class = $argument->getClass();
                    if ($class
                        && ($class->getName() === 'Symfony\Component\HttpFoundation\Request'
                            || is_subclass_of($class->getName(), 'Symfony\Component\HttpFoundation\Request')
                        )
                    ) {
                        unset($arguments[$key]);
                    }
                }

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

                if (empty($resources)) {
                    $resources[] = null;
                }

                foreach ($resources as $i => $resource) {
                    if (null !== $resource) {
                        $routeName .= '_' . basename($resource);
                    }

                    // If we already added all parent routes paths to URL & we have prefix - add it
                    if (!empty($this->prefix) && $i === count($this->parents)) {
                        $urlParts[] = $this->prefix;
                    }

                    // If we have argument for current resource, then it's object. Otherwise - it's collection
                    if (isset($arguments[$i])) {
                        if ($patternStart) {
                            $urlParts[] = strtolower($patternStart) . '/{' . $arguments[$i]->getName() . '}';
                        } elseif (null !== $resource) {
                            $urlParts[] =
                                strtolower(Pluralization::pluralize($resource)) . '/{' . $arguments[$i]->getName() . '}';
                        } else {
                            $urlParts[] ='{' . $arguments[$i]->getName() . '}';
                        }
                    } else {
                        if ($patternStart) {
                            $urlParts[] = $patternStart;
                        } elseif (null !== $resource) {
                            $urlParts[] = strtolower($resource);
                        }
                    }
                }

                // If passed method is not valid HTTP method
                // then it's either a hypertext driver,
                // a custom object (PUT) or collection (GET) method
                if (!in_array($httpMethod, $this->availableHTTPMethods)) {
                    $urlParts[] = $httpMethod;

                    // allow hypertext as the engine of application state
                    // through conventional GET actions
                    if (in_array($httpMethod, $this->availableConventionalActions)) {
                        $httpMethod = 'get';
                    } else {
                        //custom object
                        $httpMethod = 'post';

                        // resource collection
                        if (count($arguments) < count($resources)) {
                            $httpMethod = 'get';
                        }
                    }
                }

                $pattern        = implode('/', $urlParts);
                $defaults       = array('_controller' => $controllerPrefix . $method->getName(), '_format' => $this->defaultFormat);
                $requirements   = array('_method'     => strtoupper($httpMethod));
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
                //Adding in the optional _format param for serialization
                $pattern .= ".{_format}";

                // Create route with gathered parameters
                $route     = new Route($pattern, $defaults, $requirements, $options);
                $routeName = $this->namePrefix . strtolower($routeName);

                // Move custom actions at the beginning, default at the end
                if (!preg_match('/^('.implode('|', $this->availableHTTPMethods).')/', $routeName)) {
                    array_unshift($routes, array('name' => $routeName, 'route' => $route));
                } else {
                    $routes[] = array('name' => $routeName, 'route' => $route);
                }
            }
        }

        foreach ($routes as $routeInfo) {
            $collection->add($routeInfo['name'], $routeInfo['route']);
        }

        $this->prefix = null;
        $this->namePrefix = null;

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
        return is_string($resource)
            && 'rest' === $type
            && !in_array(pathinfo($resource, PATHINFO_EXTENSION), array('xml', 'yml'));
    }

    /**
     * Gets the loader resolver.
     *
     * @return LoaderResolver A LoaderResolver instance
     */
    public function getResolver()
    {
    }

    /**
     * Sets the loader resolver.
     *
     * @param LoaderResolver $resolver A LoaderResolver instance
     */
    public function setResolver(LoaderResolver $resolver)
    {
    }
}
