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
use Symfony\Component\Routing\Loader\XmlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;

use FOS\RestBundle\Routing\RestRouteCollection;
use FOS\RestBundle\Routing\Loader\RestRouteProcessor;

use Symfony\Component\Config\Util\XmlUtils;

/**
 * RestXmlCollectionLoader XML file collections loader.
 *
 * @author Donald Tyler <chekote69@gmail.com>
 */
class RestXmlCollectionLoader extends XmlFileLoader
{
    protected $collectionParents = array();

    private $processor;

    /**
     * Initializes xml loader.
     *
     * @param FileLocatorInterface $locator   locator
     * @param RestRouteProcessor   $processor route processor
     */
    public function __construct(FileLocatorInterface $locator, RestRouteProcessor $processor)
    {
        parent::__construct($locator);

        $this->processor = $processor;
    }

    /**
     * {@inheritdoc}
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

                $collection->addCollection($imported);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unable to parse tag "%s"', $node->tagName));
        }
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
        return is_string($resource) &&
            'xml' === pathinfo($resource, PATHINFO_EXTENSION) &&
            'rest' === $type;
    }

    /**
     * @param  \DOMDocument              $dom
     * @throws \InvalidArgumentException When xml doesn't validate its xsd schema
     */
    protected function validate(\DOMDocument $dom)
    {
        $location = __DIR__.'/../../Resources/config/schema/routing/rest_routing-1.0.xsd';

        $current = libxml_use_internal_errors(true);
        libxml_clear_errors();

        if (!$dom->schemaValidate($location)) {
            throw new \InvalidArgumentException(implode("\n", $this->getXmlErrors_($current)));
        }
        libxml_use_internal_errors($current);
    }

    /**
     * {@inheritDoc}
     */
    protected function loadFile($file)
    {
        if (class_exists('Symfony\Component\Config\Util\XmlUtils')) {
            return XmlUtils::loadFile($file, __DIR__ . '/../../Resources/config/schema/routing/rest_routing-1.0.xsd');
        }
 
        return parent::loadFile($file);
    }

    /**
     * Retrieves libxml errors and clears them.
     *
     * Note: The underscore postfix on the method name is to ensure compatibility with versions
     *       before 2.0.16 while working around a bug in PHP https://bugs.php.net/bug.php?id=62956
     *
     * @return array An array of libxml error strings
     */
    private function getXmlErrors_($internalErrors)
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
        libxml_use_internal_errors($internalErrors);

        return $errors;
    }

}
