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

use FOS\RestBundle\Context\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * Default View implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
final class View
{
    /**
     * @var mixed|null
     */
    private $data;

    /**
     * @var int|null
     */
    private $statusCode;

    /**
     * @var string|null
     */
    private $format;

    /**
     * @var string|null
     */
    private $location;

    /**
     * @var string|null
     */
    private $route;

    /**
     * @var array|null
     */
    private $routeParameters;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Response
     */
    private $response;

    /**
     * @param int|null $statusCode
     *
     * @return static
     */
    public static function create($data = null, $statusCode = null, array $headers = [])
    {
        return new static($data, $statusCode, $headers);
    }

    /**
     * @param string $url
     * @param int    $statusCode
     *
     * @return static
     */
    public static function createRedirect($url, $statusCode = Response::HTTP_FOUND, array $headers = [])
    {
        $view = static::create(null, $statusCode, $headers);
        $view->setLocation($url);

        return $view;
    }

    /**
     * @param string $route
     * @param int    $statusCode
     *
     * @return static
     */
    public static function createRouteRedirect(
        $route,
        array $parameters = [],
        $statusCode = Response::HTTP_FOUND,
        array $headers = []
    ) {
        $view = static::create(null, $statusCode, $headers);
        $view->setRoute($route);
        $view->setRouteParameters($parameters);

        return $view;
    }

    /**
     * @param int $statusCode
     */
    public function __construct($data = null, $statusCode = null, array $headers = [])
    {
        $this->setData($data);
        $this->setStatusCode($statusCode);

        if (!empty($headers)) {
            $this->getResponse()->headers->replace($headers);
        }
    }

    /**
     * @return View
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     *
     * @return View
     */
    public function setHeader($name, $value)
    {
        $this->getResponse()->headers->set($name, $value);

        return $this;
    }

    /**
     * @return View
     */
    public function setHeaders(array $headers)
    {
        $this->getResponse()->headers->replace($headers);

        return $this;
    }

    /**
     * @param int|null $code
     *
     * @return View
     */
    public function setStatusCode($code)
    {
        if (null !== $code) {
            $this->statusCode = $code;
        }

        return $this;
    }

    /**
     * @return View
     */
    public function setContext(Context $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @param string $format
     *
     * @return View
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * @param string $location
     *
     * @return View
     */
    public function setLocation($location)
    {
        $this->location = $location;
        $this->route = null;

        return $this;
    }

    /**
     * Sets the route (implicitly removes the location).
     *
     * @param string $route
     *
     * @return View
     */
    public function setRoute($route)
    {
        $this->route = $route;
        $this->location = null;

        return $this;
    }

    /**
     * @param array $parameters
     *
     * @return View
     */
    public function setRouteParameters($parameters)
    {
        $this->routeParameters = $parameters;

        return $this;
    }

    /**
     * @return View
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int|null
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->getResponse()->headers->all();
    }

    /**
     * @return string|null
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @return string|null
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @return array|null
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        if (null === $this->response) {
            $this->response = new Response();

            if (null !== ($code = $this->getStatusCode())) {
                $this->response->setStatusCode($code);
            }
        }

        return $this->response;
    }

    /**
     * @return Context
     */
    public function getContext()
    {
        if (null === $this->context) {
            $this->context = new Context();
        }

        return $this->context;
    }
}
