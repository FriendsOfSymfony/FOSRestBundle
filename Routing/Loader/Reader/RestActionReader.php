<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Routing\Loader\Reader;

use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Controller\Annotations\Route as RouteAnnotation;
use FOS\RestBundle\Inflector\InflectorInterface;
use FOS\RestBundle\Request\ParamReaderInterface;
use FOS\RestBundle\Routing\RestRouteCollection;
use Psr\Http\Message\MessageInterface;
use Symfony\Component\Routing\Route;

/**
 * REST controller actions reader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RestActionReader
{
    const COLLECTION_ROUTE_PREFIX = 'c';

    /**
     * @var Reader
     */
    private $annotationReader;

    /**
     * @var ParamReaderInterface
     */
    private $paramReader;

    /**
     * @var InflectorInterface
     */
    private $inflector;

    /**
     * @var array
     */
    private $formats;

    /**
     * @var bool
     */
    private $includeFormat;

    /**
     * @var string|null
     */
    private $routePrefix;

    /**
     * @var string|null
     */
    private $namePrefix;

    /**
     * @var array|string|null
     */
    private $versions;

    /**
     * @var bool|null
     */
    private $pluralize;

    /**
     * @var array
     */
    private $parents = [];

    /**
     * @var array
     */
    private $availableHTTPMethods = [
        'get',
        'post',
        'put',
        'patch',
        'delete',
        'link',
        'unlink',
        'head',
        'options',
        'mkcol',
        'propfind',
        'proppatch',
        'move',
        'copy',
        'lock',
        'unlock',
    ];

    /**
     * @var array
     */
    private $availableConventionalActions = ['new', 'edit', 'remove'];

    /**
     * @var bool
     */
    private $hasMethodPrefix;

    /**
     * Initializes controller reader.
     *
     * @param Reader               $annotationReader
     * @param ParamReaderInterface $paramReader
     * @param InflectorInterface   $inflector
     * @param bool                 $includeFormat
     * @param array                $formats
     * @param bool                 $hasMethodPrefix
     */
    public function __construct(Reader $annotationReader, ParamReaderInterface $paramReader, InflectorInterface $inflector, $includeFormat, array $formats = [], $hasMethodPrefix = true)
    {
        $this->annotationReader = $annotationReader;
        $this->paramReader = $paramReader;
        $this->inflector = $inflector;
        $this->includeFormat = $includeFormat;
        $this->formats = $formats;
        $this->hasMethodPrefix = $hasMethodPrefix;
    }

    /**
     * Sets routes prefix.
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
     * Sets route names prefix.
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
     * Sets route names versions.
     *
     * @param array|string|null $versions Route names versions
     */
    public function setVersions($versions = null)
    {
        $this->versions = (array) $versions;
    }

    /**
     * Returns versions.
     *
     * @return array|null
     */
    public function getVersions()
    {
        return $this->versions;
    }

    /**
     * Sets pluralize.
     *
     * @param bool|null $pluralize Specify if resource name must be pluralized
     */
    public function setPluralize($pluralize)
    {
        $this->pluralize = $pluralize;
    }

    /**
     * Returns pluralize.
     *
     * @return bool|null
     */
    public function getPluralize()
    {
        return $this->pluralize;
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
     * @param RestRouteCollection $collection
     * @param \ReflectionMethod   $method
     * @param string[]            $resource
     *
     * @throws \InvalidArgumentException
     *
     * @return Route
     */
    public function read(RestRouteCollection $collection, \ReflectionMethod $method, $resource)
    {
        // check that every route parent has non-empty singular name
        foreach ($this->parents as $parent) {
            if (empty($parent) || '/' === substr($parent, -1)) {
                throw new \InvalidArgumentException('Every parent controller must have `get{SINGULAR}Action(\$id)` method where {SINGULAR} is a singular form of associated object');
            }
        }

        // if method is not readable - skip
        if (!$this->isMethodReadable($method)) {
            return;
        }

        // if we can't get http-method and resources from method name - skip
        $httpMethodAndResources = $this->getHttpMethodAndResourcesFromMethod($method, $resource);
        if (!$httpMethodAndResources) {
            return;
        }

        list($httpMethod, $resources, $isCollection, $isInflectable) = $httpMethodAndResources;
        $arguments = $this->getMethodArguments($method);

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

        $routeName = $httpMethod.$this->generateRouteName($resources);
        $urlParts = $this->generateUrlParts($resources, $arguments, $httpMethod);

        // if passed method is not valid HTTP method then it's either
        // a hypertext driver, a custom object (PUT) or collection (GET)
        // method
        if (!in_array($httpMethod, $this->availableHTTPMethods)) {
            $urlParts[] = $httpMethod;
            $httpMethod = $this->getCustomHttpMethod($httpMethod, $resources, $arguments);
        }

        // generated parameters
        $routeName = strtolower($routeName);
        $path = implode('/', $urlParts);
        $defaults = ['_controller' => $method->getName()];
        $requirements = [];
        $options = [];
        $host = '';
        $versionCondition = $this->getVersionCondition();

        $annotations = $this->readRouteAnnotation($method);
        if (!empty($annotations)) {
            foreach ($annotations as $annotation) {
                $path = implode('/', $urlParts);
                $defaults = ['_controller' => $method->getName()];
                $requirements = [];
                $options = [];
                $methods = explode('|', $httpMethod);

                $annoRequirements = $annotation->getRequirements();
                $annoMethods = $annotation->getMethods();

                if (!empty($annoMethods)) {
                    $methods = $annoMethods;
                }

                $path = null !== $annotation->getPath() ? $this->routePrefix.$annotation->getPath() : $path;
                $requirements = array_merge($requirements, $annoRequirements);
                $options = array_merge($options, $annotation->getOptions());
                $defaults = array_merge($defaults, $annotation->getDefaults());
                $host = $annotation->getHost();
                $schemes = $annotation->getSchemes();
                $combinedCondition = $this->combineConditions($versionCondition, $annotation->getCondition());

                $this->includeFormatIfNeeded($path, $requirements);

                // add route to collection
                $route = new Route(
                    $path, $defaults, $requirements, $options, $host, $schemes, $methods, $combinedCondition
                );
                $this->addRoute($collection, $routeName, $route, $isCollection, $isInflectable, $annotation);
            }
        } else {
            $this->includeFormatIfNeeded($path, $requirements);

            $methods = explode('|', strtoupper($httpMethod));

            // add route to collection
            $route = new Route(
                $path, $defaults, $requirements, $options, $host, [], $methods, $versionCondition
            );
            $this->addRoute($collection, $routeName, $route, $isCollection, $isInflectable);
        }
    }

    /**
     * @return string|null
     */
    private function getVersionCondition()
    {
        if (empty($this->versions)) {
            return;
        }

        return sprintf("request.attributes.get('version') in ['%s']", implode("', '", $this->versions));
    }

    /**
     * @param string|null $conditionOne
     * @param string|null $conditionTwo
     *
     * @return string|null
     */
    private function combineConditions($conditionOne, $conditionTwo)
    {
        if (null === $conditionOne) {
            return $conditionTwo;
        }

        if (null === $conditionTwo) {
            return $conditionOne;
        }

        return sprintf('(%s) and (%s)', $conditionOne, $conditionTwo);
    }

    /**
     * Include the format in the path and requirements if its enabled.
     *
     * @param string $path
     * @param array  $requirements
     */
    private function includeFormatIfNeeded(&$path, &$requirements)
    {
        if (true === $this->includeFormat) {
            $path .= '.{_format}';

            if (!isset($requirements['_format']) && !empty($this->formats)) {
                $requirements['_format'] = implode('|', array_keys($this->formats));
            }
        }
    }

    /**
     * Checks whether provided method is readable.
     *
     * @param \ReflectionMethod $method
     *
     * @return bool
     */
    private function isMethodReadable(\ReflectionMethod $method)
    {
        // if method starts with _ - skip
        if ('_' === substr($method->getName(), 0, 1)) {
            return false;
        }

        $hasNoRouteMethod = (bool) $this->readMethodAnnotation($method, 'NoRoute');
        $hasNoRouteClass = (bool) $this->readClassAnnotation($method->getDeclaringClass(), 'NoRoute');

        $hasNoRoute = $hasNoRouteMethod || $hasNoRouteClass;
        // since NoRoute extends Route we need to exclude all the method NoRoute annotations
        $hasRoute = (bool) $this->readMethodAnnotation($method, 'Route') && !$hasNoRouteMethod;

        // if method has NoRoute annotation and does not have Route annotation - skip
        if ($hasNoRoute && !$hasRoute) {
            return false;
        }

        return true;
    }

    /**
     * Returns HTTP method and resources list from method signature.
     *
     * @param \ReflectionMethod $method
     * @param string[]          $resource
     *
     * @return bool|array
     */
    private function getHttpMethodAndResourcesFromMethod(\ReflectionMethod $method, $resource)
    {
        // if method doesn't match regex - skip
        if (!preg_match('/([a-z][_a-z0-9]+)(.*)Action/', $method->getName(), $matches)) {
            return false;
        }

        $httpMethod = strtolower($matches[1]);
        $resources = preg_split(
            '/([A-Z][^A-Z]*)/', $matches[2], -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
        );
        $isCollection = false;
        $isInflectable = true;

        if (0 === strpos($httpMethod, self::COLLECTION_ROUTE_PREFIX)
            && in_array(substr($httpMethod, 1), $this->availableHTTPMethods)
        ) {
            $isCollection = true;
            $httpMethod = substr($httpMethod, 1);
        } elseif ('options' === $httpMethod) {
            $isCollection = true;
        }

        if ($isCollection && !empty($resource)) {
            $resourcePluralized = $this->generateResourceName(end($resource));
            $isInflectable = ($resourcePluralized != $resource[count($resource) - 1]);
            $resource[count($resource) - 1] = $resourcePluralized;
        }

        $resources = array_merge($resource, $resources);

        return [$httpMethod, $resources, $isCollection, $isInflectable];
    }

    /**
     * Returns readable arguments from method.
     *
     * @param \ReflectionMethod $method
     *
     * @return \ReflectionParameter[]
     */
    private function getMethodArguments(\ReflectionMethod $method)
    {
        // ignore all query params
        $params = $this->paramReader->getParamsFromMethod($method);

        // ignore several type hinted arguments
        $ignoreClasses = [
            \Symfony\Component\HttpFoundation\Request::class,
            \FOS\RestBundle\Request\ParamFetcherInterface::class,
            \Symfony\Component\Validator\ConstraintViolationListInterface::class,
            \Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter::class,
            MessageInterface::class,
        ];

        $arguments = [];
        foreach ($method->getParameters() as $argument) {
            if (isset($params[$argument->getName()])) {
                continue;
            }

            $argumentClass = $argument->getClass();
            if ($argumentClass) {
                $className = $argumentClass->getName();
                foreach ($ignoreClasses as $class) {
                    if ($className === $class || is_subclass_of($className, $class)) {
                        continue 2;
                    }
                }
            }

            $arguments[] = $argument;
        }

        return $arguments;
    }

    /**
     * Generates final resource name.
     *
     * @param string|bool $resource
     *
     * @return string
     */
    private function generateResourceName($resource)
    {
        if (false === $this->pluralize) {
            return $resource;
        }

        return $this->inflector->pluralize($resource);
    }

    /**
     * Generates route name from resources list.
     *
     * @param string[] $resources
     *
     * @return string
     */
    private function generateRouteName(array $resources)
    {
        $routeName = '';
        foreach ($resources as $resource) {
            if (null !== $resource) {
                $routeName .= '_'.basename($resource);
            }
        }

        return $routeName;
    }

    /**
     * Generates URL parts for route from resources list.
     *
     * @param string[]               $resources
     * @param \ReflectionParameter[] $arguments
     * @param string                 $httpMethod
     *
     * @return array
     */
    private function generateUrlParts(array $resources, array $arguments, $httpMethod)
    {
        $urlParts = [];
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
                        strtolower($this->generateResourceName($resource))
                        .'/{'.$arguments[$i]->getName().'}';
                } else {
                    $urlParts[] = '{'.$arguments[$i]->getName().'}';
                }
            } elseif (null !== $resource) {
                if ((0 === count($arguments) && !in_array($httpMethod, $this->availableHTTPMethods))
                    || 'new' === $httpMethod
                    || 'post' === $httpMethod
                ) {
                    $urlParts[] = $this->generateResourceName(strtolower($resource));
                } else {
                    $urlParts[] = strtolower($resource);
                }
            }
        }

        return $urlParts;
    }

    /**
     * Returns custom HTTP method for provided list of resources, arguments, method.
     *
     * @param string                 $httpMethod current HTTP method
     * @param string[]               $resources  resources list
     * @param \ReflectionParameter[] $arguments  list of method arguments
     *
     * @return string
     */
    private function getCustomHttpMethod($httpMethod, array $resources, array $arguments)
    {
        if (in_array($httpMethod, $this->availableConventionalActions)) {
            // allow hypertext as the engine of application state
            // through conventional GET actions
            return 'get';
        }

        if (count($arguments) < count($resources)) {
            // resource collection
            return 'get';
        }

        // custom object
        return 'patch';
    }

    /**
     * Returns first route annotation for method.
     *
     * @param \ReflectionMethod $reflectionMethod
     *
     * @return RouteAnnotation[]
     */
    private function readRouteAnnotation(\ReflectionMethod $reflectionMethod)
    {
        $annotations = [];

        if ($newAnnotations = $this->readMethodAnnotations($reflectionMethod, 'Route')) {
            $annotations = array_merge($annotations, $newAnnotations);
        }

        return $annotations;
    }

    /**
     * Reads class annotations.
     *
     * @param \ReflectionClass $reflectionClass
     * @param string           $annotationName
     *
     * @return RouteAnnotation|null
     */
    private function readClassAnnotation(\ReflectionClass $reflectionClass, $annotationName)
    {
        $annotationClass = "FOS\\RestBundle\\Controller\\Annotations\\$annotationName";

        if ($annotation = $this->annotationReader->getClassAnnotation($reflectionClass, $annotationClass)) {
            return $annotation;
        }
    }

    /**
     * Reads method annotations.
     *
     * @param \ReflectionMethod $reflectionMethod
     * @param string            $annotationName
     *
     * @return RouteAnnotation|null
     */
    private function readMethodAnnotation(\ReflectionMethod $reflectionMethod, $annotationName)
    {
        $annotationClass = "FOS\\RestBundle\\Controller\\Annotations\\$annotationName";

        if ($annotation = $this->annotationReader->getMethodAnnotation($reflectionMethod, $annotationClass)) {
            return $annotation;
        }
    }

    /**
     * Reads method annotations.
     *
     * @param \ReflectionMethod $reflectionMethod
     * @param string            $annotationName
     *
     * @return RouteAnnotation[]
     */
    private function readMethodAnnotations(\ReflectionMethod $reflectionMethod, $annotationName)
    {
        $annotations = [];
        $annotationClass = "FOS\\RestBundle\\Controller\\Annotations\\$annotationName";

        if ($annotations_new = $this->annotationReader->getMethodAnnotations($reflectionMethod)) {
            foreach ($annotations_new as $annotation) {
                if ($annotation instanceof $annotationClass) {
                    $annotations[] = $annotation;
                }
            }
        }

        return $annotations;
    }

    /**
     * @param RestRouteCollection $collection
     * @param string              $routeName
     * @param Route               $route
     * @param bool                $isCollection
     * @param bool                $isInflectable
     * @param RouteAnnotation     $annotation
     */
    private function addRoute(RestRouteCollection $collection, $routeName, $route, $isCollection, $isInflectable, RouteAnnotation $annotation = null)
    {
        if ($annotation && null !== $annotation->getName()) {
            $options = $annotation->getOptions();

            if (false === $this->hasMethodPrefix || (isset($options['method_prefix']) && false === $options['method_prefix'])) {
                $routeName = $annotation->getName();
            } else {
                $routeName .= $annotation->getName();
            }
        }

        $fullRouteName = $this->namePrefix.$routeName;

        if ($isCollection && !$isInflectable) {
            $collection->add($this->namePrefix.self::COLLECTION_ROUTE_PREFIX.$routeName, $route);
            if (!$collection->get($fullRouteName)) {
                $collection->add($fullRouteName, clone $route);
            }
        } else {
            $collection->add($fullRouteName, $route);
        }
    }
}
