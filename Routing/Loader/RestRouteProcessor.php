<?php

namespace FOS\RestBundle\Routing\Loader;

use Symfony\Component\Config\Loader\FileLoader,
    Symfony\Component\Config\Loader\LoaderInterface,
    Symfony\Component\Routing\RouteCollection;

use FOS\RestBundle\Routing\Loader\RestRouteLoader;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * @author Donald Tyler <chekote69@gmail.com>
 */
class RestRouteProcessor
{
    /**
     * Import & return routes collection from a resource.
     *
     * @param   LoaderInterface   $loader     The Loader
     * @param   mixed             $resource   A Resource
     * @param   array             $parents    Array of parent resources names
     * @param   string            $prefix     Current routes prefix
     * @param   string            $namePrefix Routes names prefix
     * @param   string            $type       The resource type
     *
     * @return  RouteCollection     A RouteCollection instance
     */
    public function importResource(
        LoaderInterface $loader,
        $resource,
        array $parents = array(),
        $prefix = null,
        $namePrefix = null,
        $type = null)
    {
        $loader = $loader->resolve($resource, $type);

        if ($loader instanceof FileLoader && null !== $this->currentDir) {
            $resource = $this->getAbsolutePath($resource, $this->currentDir);
        } elseif ($loader instanceof RestRouteLoader) {
            $loader->setParents($parents);
            $loader->setPrefix($prefix);
            $loader->setRouteNamesPrefix($namePrefix);
        }

        return $loader->load($resource, $type);
    }
}
