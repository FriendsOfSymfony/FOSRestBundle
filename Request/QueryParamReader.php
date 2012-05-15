<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Request;

use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Controller\Annotations\QueryParam;

/**
 * Class loading @QueryParameter annotations from methods.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class QueryParamReader
{
    private $annotationReader;

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
     * Read annotations for a given method.
     *
     * @param \ReflectionClass $reflection Reflection class
     * @param string           $method     Method name
     *
     * @return array QueryParam annotation objects of the method. Indexed by parameter name.
     */
    public function read(\ReflectionClass $reflection, $method)
    {
        if (!$reflection->hasMethod($method)) {
            throw new \InvalidArgumentException(sprintf("Class '%s' has no method '%s' method.", $reflection->getName(), $method));
        }

        return $this->getParamsFromMethod($reflection->getMethod($method));
    }

    /**
     * Read annotations for a given method.
     *
     * @param \ReflectionMethod $method     Reflection method
     *
     * @return array QueryParam annotation objects of the method. Indexed by parameter name.
     */
    public function getParamsFromMethod(\ReflectionMethod $method)
    {
        $annotations = $this->annotationReader->getMethodAnnotations($method);

        $params = array();
        foreach ($annotations as $annotation) {
            if ($annotation instanceof QueryParam) {
                $params[$annotation->name] = $annotation;
            }
        }

        return $params;
    }
}
