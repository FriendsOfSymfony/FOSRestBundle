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

use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use FOS\RestBundle\Routing\RestRouteCollection;

/**
 * RestYamlCollectionLoader YAML file collections loader.
 */
class RestYamlCollectionLoader extends YamlFileLoader
{
    protected $collectionParents = array();
    private $processor;
    private $includeFormat;
    private $formats;
    private $defaultFormat;

    /**
     * Initializes yaml loader.
     *
     * @param FileLocatorInterface $locator
     * @param RestRouteProcessor   $processor
     * @param bool                 $includeFormat
     * @param string[]             $formats
     * @param string               $defaultFormat
     */
    public function __construct(
        FileLocatorInterface $locator,
        RestRouteProcessor $processor,
        $includeFormat = true,
        array $formats = array(),
        $defaultFormat = null
    ) {
        parent::__construct($locator);

        $this->processor = $processor;
        $this->includeFormat = $includeFormat;
        $this->formats = $formats;
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * {@inheritdoc}
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

                $imported->addPrefix($prefix);
                $collection->addCollection($imported);
            } elseif (isset($config['pattern']) || isset($config['path'])) {
                // the YamlFileLoader of the Routing component only checks for
                // the path option
                if (!isset($config['path'])) {
                    $config['path'] = $config['pattern'];
                }

                if ($this->includeFormat) {
                    // append format placeholder if not present
                    if (false === strpos($config['path'], '{_format}')) {
                        $config['path'].='.{_format}';
                    }

                    // set format requirement if configured globally
                    if (!isset($config['requirements']['_format']) && !empty($this->formats)) {
                        $config['requirements']['_format'] = implode('|', array_keys($this->formats));
                    }
                }

                // set the default format if configured
                if (null !== $this->defaultFormat) {
                    $config['defaults']['_format'] = $this->defaultFormat;
                }

                $this->parseRoute($collection, $name, $config, $path);
            } else {
                throw new \InvalidArgumentException(sprintf('Unable to parse the "%s" route.', $name));
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null)
    {
        return is_string($resource) &&
            'yml' === pathinfo($resource, PATHINFO_EXTENSION) &&
            'rest' === $type;
    }
}
