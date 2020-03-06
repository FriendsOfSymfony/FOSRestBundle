<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Routing;

use Symfony\Component\Routing\RouteCollection;

/**
 * Restful route collection.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @internal since 2.8
 */
class RestRouteCollection extends RouteCollection
{
    private $singularName = '';

    public function setSingularName(string $name): void
    {
        $this->singularName = $name;
    }

    public function getSingularName(): string
    {
        return $this->singularName;
    }

    public function prependRouteControllersWithPrefix(string $prefix): void
    {
        foreach (parent::all() as $route) {
            $route->setDefault('_controller', $prefix.$route->getDefault('_controller'));
        }
    }

    public function setDefaultFormat(?string $format): void
    {
        foreach (parent::all() as $route) {
            // Set default format only if not set already (could be defined in annotation)
            if (!$route->getDefault('_format')) {
                $route->setDefault('_format', $format);
            }
        }
    }

    /**
     * Returns routes sorted by custom HTTP methods first.
     */
    public function all(): array
    {
        $regex = '/'.
            '(_|^)'.
            '(get|post|put|delete|patch|head|options|mkcol|propfind|proppatch|lock|unlock|move|copy|link|unlink)_'. // allowed http methods
            '/i';

        $routes = parent::all();
        $customMethodRoutes = [];
        foreach ($routes as $routeName => $route) {
            if (!preg_match($regex, $routeName)) {
                $customMethodRoutes[$routeName] = $route;
                unset($routes[$routeName]);
            }
        }

        return $customMethodRoutes + $routes;
    }
}
