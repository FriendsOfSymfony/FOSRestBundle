<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\View;

use FOS\Rest\Util\Codes;

/**
 * Route based redirect implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class RouteRedirectView
{
    /**
     * Convenience method to allow for a fluent interface.
     *
     * @param string $route
     * @param mixed $data
     * @param integer $statusCode
     * @param array $headers
     */
    public static function create($route, array $data = array(), $statusCode = Codes::HTTP_CREATED, array $headers = array())
    {
        return View::create($data, $statusCode, $headers)->setRoute($route);
    }
}
