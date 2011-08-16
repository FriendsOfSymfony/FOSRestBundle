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

use FOS\RestBundle\Response\Codes;

class RouteRedirectView
{
    public static function create($route, $data = null, $statusCode = Codes::HTTP_CREATED, array $headers = array())
    {
        $headers['Location'] = $route;
        return new View($data, $statusCode, $headers);
    }
}
