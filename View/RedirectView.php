<?php

namespace FOS\RestBundle\View;

use FOS\RestBundle\Response\Codes;

class RedirectView
{
    public static function create($url, $statusCode = Codes::HTTP_FOUND, array $headers = array())
    {
        $headers['Location'] = $url;
        return new View(null, $statusCode, $headers);
    }
}
