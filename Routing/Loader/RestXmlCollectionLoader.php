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
use FOS\RestBundle\Routing\RestRouteCollection;
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
     * @param boolean              $includeFormat whether or not the requested view format must be included in the route path
     * @param string[]             $formats       supported view formats
     * @param string               $defaultFormat default view format
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

                $imported->addPrefix($prefix);
                $collection->addCollection($imported);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unable to parse tag "%s"', $node->tagName));
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function parseRoute(RouteCollection $collection, \DOMElement $node, $path)
    {
        // the Symfony Routing component uses a path attribute since Symfony 2.2
        // instead of the deprecated pattern attribute0
        if (!$node->hasAttribute('path')) {
            $node->setAttribute('path', $node->getAttribute('pattern'));
            $node->removeAttribute('pattern');
        }

        if ($this->includeFormat) {
            $path = $node->getAttribute('path');
            // append format placeholder if not present
            if (false === strpos($path, '{_format}')) {
                $node->setAttribute('path', $path.'.{_format}');
            }

            // set format requirement if configured globally
            $requirements = $node->getElementsByTagNameNS(self::NAMESPACE_URI, 'requirement');
            $format = null;
            for ($i = 0; $i < $requirements->length; $i++) {
                $item = $requirements->item($i);
                if ($item instanceof \DOMElement && $item->hasAttribute('_format')) {
                    $format = $item->getAttribute('_format');
                    break;
                }
            }
            if (null === $format && !empty($this->formats)) {
                $requirement = $node->ownerDocument->createElementNs(
                    self::NAMESPACE_URI,
                    'requirement',
                    implode('|', array_keys($this->formats))
                );
                $requirement->setAttribute('key', '_format');
                $node->appendChild($requirement);

                /*$doc =new \DOMDocument();
                $doc->appendChild($doc->importNode($node, true));
                echo $doc->saveHTML();*/
            }
        }

        // set the default format if configured
        if (null !== $this->defaultFormat) {
            $config['defaults']['_format'] = $this->defaultFormat;
            $defaultFormatNode = $node->ownerDocument->createElementNS(
                self::NAMESPACE_URI,
                'default',
                $this->defaultFormat
            );
            $defaultFormatNode->setAttribute('key', '_format');
            $node->appendChild($defaultFormatNode);
        }

        parent::parseRoute($collection, $node, $path);
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
        $restRoutinglocation = realpath(__DIR__.'/../../Resources/config/schema/routing/rest_routing-1.0.xsd');
        $routinglocation = realpath(__DIR__.'/../../Resources/config/schema/routing/routing-1.0.xsd');
        $source = <<<EOF
<?xml version="1.0" encoding="utf-8" ?>
<xsd:schema xmlns="http://symfony.com/schema"
    xmlns:xsd="http://www.w3.org/2001/XMLSchema"
    targetNamespace="http://symfony.com/schema"
    elementFormDefault="qualified">

    <xsd:import namespace="http://www.w3.org/XML/1998/namespace" />
    <xsd:import namespace="http://friendsofsymfony.github.com/schema/rest" schemaLocation="$restRoutinglocation" />
    <xsd:import namespace="http://symfony.com/schema/routing" schemaLocation="$routinglocation" />
</xsd:schema>
EOF
        ;

        $current = libxml_use_internal_errors(true);
        libxml_clear_errors();

        if (!$dom->schemaValidateSource($source)) {
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
            $dom = XmlUtils::loadFile($file);
            $this->validate($dom);
            return $dom;
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
