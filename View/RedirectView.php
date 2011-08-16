<?php

namespace FOS\RestBundle\View;

class RedirectView extends View
{
    public static function create($url, $statusCode = Codes::HTTP_FOUND)
    {
        return new self($url, $statusCode);
    }

    public function __construct($url, $statusCode = Codes::HTTP_FOUND)
    {
        return View::create(null, $statusCode)->setLocation($url);
    }
}