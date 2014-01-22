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

use Symfony\Component\Config\Resource\FileResource;
use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Routing\RestRouteCollection;

/**
 * REST controller reader.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 */
class RestControllerReader
{
    private $actionReader;
    private $annotationReader;

    /**
     * Initializes controller reader.
     *
     * @param RestActionReader $actionReader     action reader
     * @param Reader           $annotationReader annotation reader
     */
    public function __construct(RestActionReader $actionReader, Reader $annotationReader)
    {
        $this->actionReader     = $actionReader;
        $this->annotationReader = $annotationReader;
    }

    /**
     * Returns action reader.
     *
     * @return RestActionReader
     */
    public function getActionReader()
    {
        return $this->actionReader;
    }

    /**
     * Reads controller routes.
     *
     * @param ReflectionClass $reflection
     *
     * @return RestRouteCollection
     */
    public function read(\ReflectionClass $reflection)
    {
        $collection = new RestRouteCollection();
        $collection->addResource(new FileResource($reflection->getFileName()));

        // read prefix annotation
        if ($annotation = $this->readClassAnnotation($reflection, 'Prefix')) {
            $this->actionReader->setRoutePrefix($annotation->value);
        }

        // read name-prefix annotation
        if ($annotation = $this->readClassAnnotation($reflection, 'NamePrefix')) {
            $this->actionReader->setNamePrefix($annotation->value);
        }

        $resource = array();
        // read route-resource annotation
        if ($annotation = $this->readClassAnnotation($reflection, 'RouteResource')) {
            $resource = explode('_', $annotation->resource);
        } elseif ($reflection->implementsInterface('FOS\RestBundle\Routing\ClassResourceInterface')) {
            $resource  = preg_split(
                '/([A-Z][^A-Z]*)Controller/', $reflection->getShortName(), -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE
            );
            if (empty($resource)) {
                throw new \InvalidArgumentException("Controller '{$reflection->name}' does not identify a resource");
            }
        }

        // trim '/' at the start of the prefix
        if ('/' === substr($prefix = $this->actionReader->getRoutePrefix(), 0, 1)) {
            $this->actionReader->setRoutePrefix(substr($prefix, 1));
        }

        // read action routes into collection
        foreach ($reflection->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            $this->actionReader->read($collection, $method, $resource);
        }

        $this->actionReader->setRoutePrefix(null);
        $this->actionReader->setNamePrefix(null);
        $this->actionReader->setParents(array());

        return $collection;
    }

    /**
     * Reads class annotations.
     *
     * @param ReflectionClass $reflection     controller class
     * @param string          $annotationName annotation name
     *
     * @return Annotation|null
     */
    private function readClassAnnotation(\ReflectionClass $reflection, $annotationName)
    {
        $annotationClass = "FOS\\RestBundle\\Controller\\Annotations\\$annotationName";

        if ($annotation = $this->annotationReader->getClassAnnotation($reflection, $annotationClass)) {
            return $annotation;
        }
    }
}
