<?php

namespace FOS\RestBundle\View;

use FOS\RestBundle\Response\Codes;

class RouteRedirectView
{
    public static function create($route, array $parameters = array(), $statusCode = Codes::HTTP_CREATED, $headers = array())
    {
        $headers['Location'] = $route;
        return new View($parameters, $statusCode, $headers);
    }
}
