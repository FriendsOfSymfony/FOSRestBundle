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

@trigger_error(sprintf('The %s\RestRouteCollection class is deprecated since FOSRestBundle 2.8.', __NAMESPACE__), E_USER_DEPRECATED);

use Symfony\Component\Routing\RouteCollection;

/**
 * Restful route collection.
 *
 * @author Konstantin Kudryashov <ever.zet@gmail.com>
 *
 * @deprecated since 2.8
 */
class RestRouteCollection extends RouteCollection
{
    private $singularName;

    /**
     * @param string $name
     */
    public function setSingularName($name)
    {
        $this->singularName = $name;
    }

    /**
     * @return string
     */
    public function getSingularName()
    {
        return $this->singularName;
    }

    /**
     * @param string $prefix
     */
    public function prependRouteControllersWithPrefix($prefix)
    {
        foreach (parent::all() as $route) {
            $route->setDefault('_controller', $prefix.$route->getDefault('_controller'));
        }
    }

    /**
     * @param string $format
     */
    public function setDefaultFormat($format)
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
     *
     * @return array
     */
    public function all()
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
