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

use FOS\RestBundle\Controller\Annotations\Param;

/**
 * interface for loading query parameters for a method
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
interface ParamReaderInterface
{
    /**
     * Read annotations for a given method.
     *
     * @param \ReflectionClass $reflection Reflection class
     * @param string           $method     Method name
     *
     * @return Param[] Param annotation objects of the method. Indexed by parameter name.
     */
    public function read(\ReflectionClass $reflection, $method);

    /**
     * Read annotations for a given method.
     *
     * @param \ReflectionMethod $method Reflection method
     *
     * @return Param[] Param annotation objects of the method. Indexed by parameter name.
     */
    public function getParamsFromMethod(\ReflectionMethod $method);

    /**
     *
     * @param \ReflectionClass $class
     *
     * @return Param[] Param annotation objects of the class. Indexed by parameter name.
     */
    public function getParamsFromClass(\ReflectionClass $class);
}
