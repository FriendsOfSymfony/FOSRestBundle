<?php

namespace FOS\RestBundle\Routing;

use Symfony\Component\Routing\RouteCollection;

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
