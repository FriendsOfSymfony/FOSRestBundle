<?php

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
