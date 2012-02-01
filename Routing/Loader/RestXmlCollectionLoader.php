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
    Symfony\Component\Routing\Loader\XmlFileLoader,
    Symfony\Component\Config\Loader\FileLoader,
    Symfony\Component\Routing\RouteCollection,
    Symfony\Component\Routing\Route;

use FOS\RestBundle\Routing\RestRouteCollection,
    FOS\RestBundle\Routing\Loader\RestRouteProcessor;

/**
 * RestXmlCollectionLoader XML file collections loader.
 *
 * @author Donald Tyler <chekote69@gmail.com>
 */
class RestXmlCollectionLoader extends XmlFileLoader
{
    protected $collectionParents = array();

    private $processor;

    public function __construct(FileLocatorInterface $locator, RestRouteProcessor $processor)
    {
        parent::__construct($locator);

        $this->processor = $processor;
    }

    /**
     * @inheritDoc
     */
    protected function parseNode(RouteCollection $collection, \DOMElement $node, $path, $file)
    {
        switch ($node->tagName) {
            case 'route':
                $this->parseRoute($collection, $node, $path);
                break;
            case 'import':
                $name       = (string) $node->getAttribute('id');
                $resource   = (string) $node->getAttribute('resource');
                $prefix     = (string) $node->getAttribute('prefix');
                $namePrefix = (string) $node->getAttribute('name-prefix');
                $parent     = (string) $node->getAttribute('parent');
                $type       = (string) $node->getAttribute('type');
                $currentDir = dirname($path);

                $parents = array();
                if (!empty($parent)) {
                    if (!isset($this->collectionParents[$parent])) {
                        throw new \InvalidArgumentException(sprintf('Cannot find parent resource with name %s', $parent));
                    }

                    $parents = $this->collectionParents[$parent];
                }

                $imported = $this->processor->importResource($this, $resource, $parents, $prefix, $namePrefix, $type, $currentDir);

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
     * @throws \InvalidArgumentException When xml doesn't validate its xsd schema
     */
    protected function validate(\DOMDocument $dom)
    {
        $schema = __DIR__.'/../../Resources/config/schema/routing/rest_routing-1.0.xsd';

        $current = libxml_use_internal_errors(true);
        if (!$dom->schemaValidate($schema)) {
            throw new \InvalidArgumentException(implode("\n", $this->getXmlErrors()));
        }
        libxml_use_internal_errors($current);
    }

    /**
     * Retrieves libxml errors and clears them.
     *
     * @return array An array of libxml error strings
     */
    private function getXmlErrors()
    {
        $errors = array();
        foreach (libxml_get_errors() as $error) {
            $errors[] = sprintf('[%s %s] %s (in %s - line %d, column %d)',
                LIBXML_ERR_WARNING == $error->level ? 'WARNING' : 'ERROR',
                $error->code,
                trim($error->message),
                $error->file ? $error->file : 'n/a',
                $error->line,
                $error->column
            );
        }

        libxml_clear_errors();

        return $errors;
    }
}
