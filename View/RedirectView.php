<?php

namespace FOS\RestBundle\View;

use FOS\RestBundle\Response\Codes;

class RedirectView
{
    public static function create($url, $statusCode = Codes::HTTP_FOUND, $headers = array())
    {
        return new View($url, $statusCode, $headers);
    }
}
