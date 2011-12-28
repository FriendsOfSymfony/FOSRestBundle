<?php

namespace FOS\RestBundle\Routing\Loader\Reader;

use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Util\Pluralization;
use Symfony\Component\Routing\Route;
use FOS\RestBundle\Routing\RestRouteCollection;

/**
 * RestActionReader.
 */
class RestActionReader
{
    private $annotationReader;

    private $routePrefix;
    private $namePrefix;
    private $parents = array();

    private $availableHTTPMethods = array('get', 'post', 'put', 'patch', 'delete', 'head');
    private $availableConventionalActions = array('new', 'edit', 'remove');

    /**
     * Initializes controller reader.
     *
     * @param Reader $annotationReader annotation reader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * Set routes prefix.
     *
     * @param string $prefix Routes prefix
     */
    public function setRoutePrefix($prefix = null)
    {
        $this->routePrefix = $prefix;
    }

    /**
     * Returns route prefix.
     *
     * @return string
     */
    public function getRoutePrefix()
    {
        return $this->routePrefix;
    }

    /**
     * Set route names prefix.
     *
     * @param string $prefix Route names prefix
     */
    public function setNamePrefix($prefix = null)
    {
        $this->namePrefix = $prefix;
    }

    /**
     * Returns name prefix.
     *
     * @return string
     */
    public function getNamePrefix()
    {
        return $this->namePrefix;
    }

    /**
     * Set parent routes.
     *
     * @param array $parents Array of parent resources names
     */
    public function setParents(array $parents)
    {
        $this->parents = $parents;
    }

    /**
     * Returns parents.
     *
     * @return array
     */
    public function getParents()
    {
        return $this->parents;
    }

    /**
     * Reads action route.
     *
     * @param RestRouteCollection $collection route collection to read into
     * @param \ReflectionMethod   $method     method reflection
     *
     * @return Route
     */
    public function read(RestRouteCollection $collection, \ReflectionMethod $method)
    {
        // check that every route parent has non-empty singular name
        foreach ($this->parents as $parent) {
            if (empty($parent) || '/' === substr($parent, -1)) {
                throw new \InvalidArgumentException(
                    'All parent controllers must have ::getSINGULAR_NAME() action'
                );
            }
        }

        // if method starts with _ - skip
        if ('_' === substr($method->getName(), 0, 1)) {
            return;
        }
        // if method has NoRoute annotation - skip
        if ($annotation = $this->readMethodAnnotation($method, 'NoRoute')) {
            return;
        }
        // if method doesn't match regex - skip
        if (!preg_match('/([a-z][_a-z0-9]+)(.*)Action/', $method->getName(), $matches)) {
            return;
        }

        $httpMethod = strtolower($matches[1]);
        $resources  = preg_split(
            '/([A-Z][^A-Z]*)/', $matches[2], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );

        // ignore arguments that are or extend from Symfony\Component\HttpFoundation\Request
        $arguments = array();
        foreach ($method->getParameters() as $argument) {
            if ($argumentClass = $argument->getClass()) {
                if ($argumentClass->getName() === 'Symfony\Component\HttpFoundation\Request'
                 || $argumentClass->isSubclassOf('Symfony\Component\HttpFoundation\Request')) {
                     continue;
                }
            }

            $arguments[] = $argument;
        }

        // if we have only 1 resource & 1 argument passed, then it's object call, so
        // we can set collection singular name
        if (1 === count($resources) && 1 === count($arguments) - count($this->parents)) {
            $collection->setSingularName($resources[0]);
        }

        // if we have parents passed - merge them with own resource names
        if (count($this->parents)) {
            $resources = array_merge($this->parents, $resources);
        }

        if (empty($resources)) {
            $resources[] = null;
        }

        // generate route name
        $routeName = $httpMethod;
        foreach ($resources as $resource) {
            if (null !== $resource) {
                $routeName .= '_' . basename($resource);
            }
        }

        // generate URL parts
        $urlParts = array();
        foreach ($resources as $i => $resource) {
            // if we already added all parent routes paths to URL & we have
            // prefix - add it
            if (!empty($this->routePrefix) && $i === count($this->parents)) {
                $urlParts[] = $this->routePrefix;
            }

            // if we have argument for current resource, then it's object.
            // otherwise - it's collection
            if (isset($arguments[$i])) {
                if (null !== $resource) {
                    $urlParts[] =
                        strtolower(Pluralization::pluralize($resource))
                        .'/{'.$arguments[$i]->getName().'}';
                } else {
                    $urlParts[] =
                        '{'.$arguments[$i]->getName().'}';
                }
            } elseif (null !== $resource) {
                $urlParts[] = strtolower($resource);
            }
        }

        // if passed method is not valid HTTP method then it's either
        // a hypertext driver, a custom object (PUT) or collection (GET)
        // method
        if (!in_array($httpMethod, $this->availableHTTPMethods)) {
            $urlParts[] = $httpMethod;

            if (in_array($httpMethod, $this->availableConventionalActions)) {
                // allow hypertext as the engine of application state
                // through conventional GET actions
                $httpMethod = 'get';
            } elseif (count($arguments) < count($resources)) {
                // resource collection
                $httpMethod = 'get';
            } else {
                //custom object
                $httpMethod = 'post';
            }
        }

        // generated parameters:
        // TODO: $controllerPrefix, _format => defaultFormat, sort
        $routeName      = $this->namePrefix.strtolower($routeName);
        $pattern        = implode('/', $urlParts);
        $defaults       = array('_controller' => $method->getName());
        $requirements   = array('_method' => strtoupper($httpMethod));
        $options        = array();

        // read method annotations
        foreach (array('Route','Get','Post','Put','Patch','Delete','Head') as $annotationName) {
            if ($annotation = $this->readMethodAnnotation($method, $annotationName)) {
                $annoRequirements = $annotation->getRequirements();

                if (!isset($annoRequirements['_method']) || null === $annoRequirements['_method']) {
                    $annoRequirements['_method'] = $requirements['_method'];
                }

                $pattern      = $annotation->getPattern() ?: $pattern;
                $requirements = array_merge($requirements, $annoRequirements);
                $options      = array_merge($options, $annotation->getOptions());
                $defaults     = array_merge($defaults, $annotation->getDefaults());

                break;
            }
        }

        // add route to collection
        $collection->add($routeName, new Route(
            $pattern.'.{_format}', $defaults, $requirements, $options
        ));
    }

    /**
     * Reads method annotations.
     *
     * @param ReflectionMethod $reflection     controller action
     * @param string           $annotationName annotation name
     *
     * @return Annotation|null
     */
    private function readMethodAnnotation(\ReflectionMethod $reflection, $annotationName)
    {
        $annotationClass = "FOS\\RestBundle\\Controller\\Annotations\\$annotationName";

        if ($annotation = $this->annotationReader->getMethodAnnotation($reflection, $annotationClass)) {
            return $annotation;
        }
    }
}
