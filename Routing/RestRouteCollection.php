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

    /**
     * Set collection singular name.
     *
     * @param   string  $name   Singular name
     */
    public function setSingularName($name)
    {
        $this->singularName = $name;
    }

    /**
     * Get collection singular name.
     */
    public function getSingularName()
    {
        return $this->singularName;
    }
}
