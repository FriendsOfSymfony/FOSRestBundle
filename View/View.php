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

use FOS\RestBundle\Util\Codes;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\Response;
use JMS\Serializer\SerializationContext;

/**
 * Default View implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class View
{
    private $data;
    private $template;
    private $templateVar;
    private $engine;
    private $format;
    private $location;
    private $route;
    private $routeParameters;

    /**
     * @var SerializationContext
     */
    private $serializationContext;

    /**
     * @var Response
     */
    private $response;

    /**
     * Convenience method to allow for a fluent interface.
     *
     * @param mixed $data
     * @param int   $statusCode
     * @param array $headers
     *
     * @return \FOS\RestBundle\View\View
     */
    public static function create($data = null, $statusCode = null, array $headers = array())
    {
        return new static($data, $statusCode, $headers);
    }

    /**
     * Convenience method to allow for a fluent interface while creating a redirect to a
     * given url.
     *
     * @param string $url
     * @param int    $statusCode
     * @param array  $headers
     *
     * @return View
     */
    public static function createRedirect($url, $statusCode = Codes::HTTP_FOUND, array $headers = array())
    {
        $view = static::create(null, $statusCode, $headers);
        $view->setLocation($url);

        return $view;
    }

    /**
     * Convenience method to allow for a fluent interface while creating a redirect to a
     * given route.
     *
     * @param string $route
     * @param array  $parameters
     * @param int    $statusCode
     * @param array  $headers
     *
     * @return View
     */
    public static function createRouteRedirect(
        $route,
        array $parameters = array(),
        $statusCode = Codes::HTTP_FOUND,
        array $headers = array()
    ) {
        $view = static::create(null, $statusCode, $headers);
        $view->setRoute($route);
        $view->setRouteParameters($parameters);

        return $view;
    }

    /**
     * Constructor.
     *
     * @param mixed $data
     * @param int   $statusCode
     * @param array $headers
     */
    public function __construct($data = null, $statusCode = null, array $headers = array())
    {
        $this->setData($data);
        $this->setStatusCode($statusCode ?: 200);
        $this->setTemplateVar('data');

        if (!empty($headers)) {
            $this->getResponse()->headers->replace($headers);
        }
    }

    /**
     * Sets the data.
     *
     * @param mixed $data
     *
     * @return View
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Sets a header.
     *
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
     * Sets the headers.
     *
     * @param array $headers
     *
     * @return View
     */
    public function setHeaders(array $headers)
    {
        $this->getResponse()->headers->replace($headers);

        return $this;
    }

    /**
     * Sets the HTTP status code.
     *
     * @param int $code
     *
     * @return View
     */
    public function setStatusCode($code)
    {
        $this->getResponse()->setStatusCode($code);

        return $this;
    }

    /**
     * Sets the serialization context.
     *
     * @param SerializationContext $serializationContext
     *
     * @return View
     */
    public function setSerializationContext(SerializationContext $serializationContext)
    {
        $this->serializationContext = $serializationContext;

        return $this;
    }

    /**
     * Sets template to use for the encoding.
     *
     * @param string|TemplateReference $template
     *
     * @return View
     *
     * @throws \InvalidArgumentException if the template is neither a string nor an instance of TemplateReference
     */
    public function setTemplate($template)
    {
        if (!(is_string($template) || $template instanceof TemplateReference)) {
            throw new \InvalidArgumentException('The template should be a string or extend TemplateReference');
        }
        $this->template = $template;

        return $this;
    }

    /**
     * Sets template variable name to be used in templating formats.
     *
     * @param string
     *
     * @return View
     */
    public function setTemplateVar($templateVar)
    {
        $this->templateVar = $templateVar;

        return $this;
    }

    /**
     * Sets the engine.
     *
     * @param $engine
     *
     * @return View
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * Sets the format.
     *
     * @param $format
     *
     * @return View
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Sets the location (implicitly removes the route).
     *
     * @param $location
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
     * @param $route
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
     * Sets route data.
     *
     * @param mixed $parameters
     *
     * @return View
     */
    public function setRouteParameters($parameters)
    {
        $this->routeParameters = $parameters;

        return $this;
    }

    /**
     * Sets the response.
     *
     * @param Response $response
     *
     * @return View
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Gets the data.
     *
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int|null
     */
    public function getStatusCode()
    {
        return $this->getResponse()->getStatusCode();
    }

    /**
     * Gets the headers.
     *
     * @return array|null
     */
    public function getHeaders()
    {
        return $this->getResponse()->headers->all();
    }

    /**
     * Gets the template.
     *
     * @return TemplateReference|string|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Gets the template variable name.
     *
     * @return string|null
     */
    public function getTemplateVar()
    {
        return $this->templateVar;
    }

    /**
     * Gets the engine.
     *
     * @return string|null
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Gets the format.
     *
     * @return string|null
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Gets the location.
     *
     * @return string|null
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * Gets the route.
     *
     * @return string|null
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Gets route parameters.
     *
     * @return string|null
     */
    public function getRouteParameters()
    {
        return $this->routeParameters;
    }

    /**
     * Gets the response.
     *
     * @return Response
     */
    public function getResponse()
    {
        if (null === $this->response) {
            $this->response = new Response();
        }

        return $this->response;
    }

    /**
     * Gets the serialization context.
     *
     * @return SerializationContext
     */
    public function getSerializationContext()
    {
        if (null === $this->serializationContext) {
            $this->serializationContext = new SerializationContext();
        }

        return $this->serializationContext;
    }
}
