<?php

namespace FOS\RestBundle\Routing\Loader;

use Symfony\Component\Config\Resource\FileResource,
    Symfony\Component\Routing\Loader\XmlFileLoader,
    Symfony\Component\Routing\RouteCollection,
    Symfony\Component\Routing\Route;

use FOS\RestBundle\Routing\RestRouteCollection;

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
 * RestXmlCollectionLoader XML file collections loader.
 */
class RestXmlCollectionLoader extends XmlFileLoader
{
    protected $collectionParents = array();

    /**
     * Loads a Xml collection file.
     *
     * @param  string $file A Xml file path
     * @param  string $type The resource type
     *
     * @return RouteCollection A RouteCollection instance
     *
     * @throws \InvalidArgumentException When tag can't be parsed
     */
    public function load($file, $type = null)
    {
        $path = $this->locator->locate($file);

        $xml = $this->loadFile($path);

        $collection = new RouteCollection();
        $collection->addResource(new FileResource($path));

        // process routes and imports
        foreach ($xml->documentElement->childNodes as $node) {
            if (!$node instanceof \DOMElement) {
                continue;
            }

            switch ($node->tagName) {
                case 'route':
                    $this->parseRoute($collection, $node, $path);
                    break;
                case 'import':
                    $this->currentDir = dirname($path);

                    $name       = (string) $node->getAttribute('id');
                    $resource   = (string) $node->getAttribute('resource');
                    $prefix     = (string) $node->getAttribute('prefix');
                    $namePrefix = (string) $node->getAttribute('name-prefix');
                    $parent     = (string) $node->getAttribute('parent');
                    $type       = (string) $node->getAttribute('type');

                    $parents = array();
                    if (!empty($parent)) {
                        if (!isset($this->collectionParents[$parent])) {
                            throw new \InvalidArgumentException(sprintf('Can not find parent resource with name %s', $parent));
                        }

                        $parents = $this->collectionParents[$parent];
                    }

                    $imported = $this->importResource($resource, $parents, $prefix, $namePrefix, $type);

                    if (!empty($name) && $imported instanceof RestRouteCollection) {
                        $parents[]  = (!empty($prefix) ? $prefix . '/' : '') . $imported->getSingularName();
                        $prefix     = null;

                        $this->collectionParents[$name] = $parents;
                    }

                    $collection->addCollection($imported, $prefix);
                    break;
                default:
                    throw new \InvalidArgumentException(sprintf('Unable to parse tag "%s"', $node->tagName));
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
            'xml' === pathinfo($resource, PATHINFO_EXTENSION) &&
            'rest' === $type;
    }

    /**
     * Import & return routes collection from a resource.
     *
     * @param   mixed   $resource   A Resource
     * @param   array   $parents    Array of parent resources names
     * @param   string  $prefix     Current routes prefix
     * @param   string  $namePrefix Routes names prefix
     * @param   string  $type       The resource type
     *
     * @return  RouteCollection     A RouteCollection instance
     */
    protected function importResource($resource, array $parents = array(), $prefix = null,
                                      $namePrefix = null, $type = null)
    {
        $loader = $this->resolve($resource, $type);

        if ($loader instanceof FileLoader && null !== $this->currentDir) {
            $resource = $this->getAbsolutePath($resource, $this->currentDir);
        } elseif ($loader instanceof RestRouteLoader) {
            $loader->setParents($parents);
            $loader->setPrefix($prefix);
            $loader->setRouteNamesPrefix($namePrefix);
        }

        return $loader->load($resource, $type);
    }

    /**
     * @throws \InvalidArgumentException When xml doesn't validate its xsd schema
     */
    protected function validate(\DOMDocument $dom)
    {
        $parts = explode('/', str_replace('\\', '/', __DIR__.'/../../Resources/config/schema/routing/rest_routing-1.0.xsd'));
        $drive = '\\' === DIRECTORY_SEPARATOR ? array_shift($parts).'/' : '';
        $location = 'file:///'.$drive.implode('/', $parts);

        $current = libxml_use_internal_errors(true);
        if (!$dom->schemaValidate($location)) {
            throw new \InvalidArgumentException(implode("\n", $this->getXmlErrors()));
        }
        libxml_use_internal_errors($current);
    }
}
