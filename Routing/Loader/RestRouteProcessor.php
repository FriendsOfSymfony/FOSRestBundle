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

use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RouteCollection;

/**
 * Processes resource in provided loader.
 *
 * @author Donald Tyler <chekote69@gmail.com>
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @internal since 2.8
 */
class RestRouteProcessor
{
    public function importResource(
        LoaderInterface $loader,
        $resource,
        array $parents = [],
        string $routePrefix = null,
        string $namePrefix = null,
        string $type = null,
        string $currentDir = null
    ): RouteCollection {
        $loader = $loader->resolve($resource, $type);

        if ($loader instanceof FileLoader && null !== $currentDir) {
            $resource = $loader->getLocator()->locate($resource, $currentDir);
        } elseif ($loader instanceof RestRouteLoader) {
            $loader->getControllerReader()->getActionReader()->setParents($parents);
            $loader->getControllerReader()->getActionReader()->setRoutePrefix($routePrefix);
            $loader->getControllerReader()->getActionReader()->setNamePrefix($namePrefix);
        }

        return $loader->load($resource, $type);
    }
}
