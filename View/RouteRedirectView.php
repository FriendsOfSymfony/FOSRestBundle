<?php

namespace FOS\RestBundle\View;

class RouteRedirectView
{
    private $route;
    private $parameters;
    private $statusCode;

    public static function createResourceRedirect($route, array $parameters = array(), $statusCode = Codes::HTTP_CREATED)
    {
        return new self($route, $parameters, $statusCode);
    }

    public function __construct($route, array $parameters, $statusCode = Codes::HTTP_FOUND)
    {
        $this->route = $route;
        $this->parameters = $parameters;
        $this->statusCode = $statusCode;
    }

    public function getRoute()
    {
        return $this->route;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }
}