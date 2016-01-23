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
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * Default View implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class View
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
     * @var mixed|null
     */
    private $templateData = [];

    /**
     * @var TemplateReference|string|null
     */
    private $template;

    /**
     * @var string|null
     */
    private $templateVar;

    /**
     * @var string|null
     */
    private $engine;

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
     * Convenience method to allow for a fluent interface.
     *
     * @param mixed $data
     * @param int   $statusCode
     * @param array $headers
     *
     * @return static
     */
    public static function create($data = null, $statusCode = null, array $headers = [])
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
     * @return static
     */
    public static function createRedirect($url, $statusCode = Response::HTTP_FOUND, array $headers = [])
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
     * Constructor.
     *
     * @param mixed $data
     * @param int   $statusCode
     * @param array $headers
     */
    public function __construct($data = null, $statusCode = null, array $headers = [])
    {
        $this->setData($data);
        $this->setStatusCode($statusCode);
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
     * Set template variable.
     *
     * @param array|callable $data
     *
     * @return View
     */
    public function setTemplateData($data = [])
    {
        $this->templateData = $data;

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
     * Sets the serialization context.
     *
     * @param Context $context
     *
     * @return View
     */
    public function setContext(Context $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Sets template to use for the encoding.
     *
     * @param string|TemplateReferenceInterface $template
     *
     * @return View
     *
     * @throws \InvalidArgumentException if the template is neither a string nor an instance of TemplateReferenceInterface
     */
    public function setTemplate($template)
    {
        if (!(is_string($template) || $template instanceof TemplateReferenceInterface)) {
            throw new \InvalidArgumentException('The template should be a string or implement TemplateReferenceInterface');
        }
        $this->template = $template;

        return $this;
    }

    /**
     * Sets template variable name to be used in templating formats.
     *
     * @param string $templateVar
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
     * @param string $engine
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
     * Sets the location (implicitly removes the route).
     *
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
     * Sets route data.
     *
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
     * Gets the template data.
     *
     * @return mixed|null
     */
    public function getTemplateData()
    {
        return $this->templateData;
    }

    /**
     * Gets the HTTP status code.
     *
     * @return int|null
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Gets the headers.
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->getResponse()->headers->all();
    }

    /**
     * Gets the template.
     *
     * @return TemplateReferenceInterface|string|null
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
     * @return array|null
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

            if (null !== ($code = $this->getStatusCode())) {
                $this->response->setStatusCode($code);
            }
        }

        return $this->response;
    }

    /**
     * Gets the serialization context.
     *
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
