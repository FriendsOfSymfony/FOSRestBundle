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

use FOS\RestBundle\Util\LegacyCodesHelper;

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
     * @param array  $parameters
     * @param int    $statusCode
     * @param array  $headers
     *
     * @return View
     *
     * @deprecated To be removed in FOSRestBundle 2.0.0. Use View::createRouteRedirect instead.
     */
    public static function create($route, array $parameters = array(), $statusCode = null, array $headers = array())
    {
        if ($statusCode === null) {
            $statusCode = LegacyCodesHelper::get('HTTP_CREATED');
        }

        return View::createRouteRedirect($route, $parameters, $statusCode, $headers);
    }
}
