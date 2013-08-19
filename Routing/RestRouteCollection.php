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
 */
class RestRouteCollection extends RouteCollection
{
    private $singularName;
    private $subject;
    private $identifier;
    private $isFormatInRoute = false;

    /**
     * Set collection singular name.
     *
     * @param string $name Singular name
     */
    public function setSingularName($name)
    {
        $this->singularName = $name;
    }

    /**
     * Returns collection singular name.
     *
     * @return string
     */
    public function getSingularName()
    {
        return $this->singularName;
    }

    /**
     * Set route subject class.
     *
     * @param string $subject route subject class
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * Set route subject class.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set subject identifier.
     *
     * @param string $identifier route identifier class
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Set subject identifier.
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Check if the format should be set in the route
     *
     * @return Boolean
     */
    public function isFormatInRoute()
    {
        return $this->isFormatInRoute;
    }

    /**
     * Adds controller prefix to all collection routes.
     *
     * @param string $prefix
     */
    public function prependRouteControllersWithPrefix($prefix)
    {
        foreach (parent::all() as $route) {
            $route->setDefault('_controller', $prefix.$route->getDefault('_controller'));
        }
    }

    /**
     * Sets default format of routes.
     *
     * @param string $format
     */
    public function setDefaultFormat($format)
    {
        $this->isFormatInRoute = true;
        foreach (parent::all() as $route) {
            $route->setDefault('_format', $format);
        }
    }

    /**
     * Returns routes sorted by custom HTTP methods first.
     *
     * @return array
     */
    public function all()
    {
        $routes = parent::all();
        $customMethodRoutes = array();
        foreach ($routes as $routeName => $route) {

            if ( ! preg_match('/(_|^)(get|post|put|delete|patch|head|options)_/', $routeName)) {
                $customMethodRoutes[$routeName] = $route;
                unset($routes[$routeName]);
            }
        }

        return $customMethodRoutes + $routes;
    }
}
