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

use Symfony\Component\Config\FileLocatorInterface,
    Symfony\Component\Config\Resource\FileResource,
    Symfony\Component\Yaml\Yaml,
    Symfony\Component\Routing\Loader\YamlFileLoader,
    Symfony\Component\Config\Loader\FileLoader,
    Symfony\Component\Routing\RouteCollection,
    Symfony\Component\Routing\Route;

use FOS\RestBundle\Routing\RestRouteCollection,
    FOS\RestBundle\Routing\Loader\RestRouteProcessor;

/**
 * RestYamlCollectionLoader YAML file collections loader.
 */
class RestYamlCollectionLoader extends YamlFileLoader
{
    protected $collectionParents = array();

    private $processor;

    public function __construct(FileLocatorInterface $locator, RestRouteProcessor $processor)
    {
        parent::__construct($locator);

        $this->processor = $processor;
    }

    /**
     * Loads a Yaml collection file.
     *
     * @param  string $file A Yaml file path
     * @param  string $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When route can't be parsed
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $config = Yaml::parse($path);

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // process routes and imports
        foreach ($config as $name => $config) {
            if (isset($config['resource'])) {
                $resource   = $config['resource'];
                $prefix     = isset($config['prefix'])      ? $config['prefix']         : null;
                $namePrefix = isset($config['name_prefix']) ? $config['name_prefix']    : null;
                $parent     = isset($config['parent'])      ? $config['parent']         : null;
                $type       = isset($config['type'])        ? $config['type']           : null;
                $currentDir = dirname($path);

                $parents = array();
                if (!empty($parent)) {
                    if (!isset($this->collectionParents[$parent])) {
                        throw new \InvalidArgumentException(sprintf('Cannot find parent resource with name %s', $parent));
                    }

                    $parents = $this->collectionParents[$parent];
                }

                $imported = $this->processor->importResource($this, $resource, $parents, $prefix, $namePrefix, $type, $currentDir);

                if ($imported instanceof RestRouteCollection) {
                    $parents[]  = ($prefix ? $prefix . '/' : '') . $imported->getSingularName();
                    $prefix     = null;

                    $this->collectionParents[$name] = $parents;
                }

                $collection->addCollection($imported, $prefix);
            } elseif (isset($config['pattern'])) {
                $this->parseRoute($collection, $name, $config, $path);
            } else {
                throw new \InvalidArgumentException(sprintf('Unable to parse the "%s" route.', $name));
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
            'yml' === pathinfo($resource, PATHINFO_EXTENSION) &&
            'rest' === $type;
    }
}
