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
 *
 * @final since 2.8
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
        $this->setTemplateVar('data', false);

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
     * @deprecated since 2.8
     *
     * @param array|callable $data
     *
     * @return View
     */
    public function setTemplateData($data = [])
    {
        if (1 === func_num_args() || func_get_arg(1)) {
            @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);
        }

        $this->templateData = $data;

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
     * @deprecated since 2.8
     *
     * @param string|TemplateReferenceInterface $template
     *
     * @return View
     *
     * @throws \InvalidArgumentException if the template is neither a string nor an instance of TemplateReferenceInterface
     */
    public function setTemplate($template)
    {
        if (1 === func_num_args() || func_get_arg(1)) {
            @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);
        }

        if (!(is_string($template) || $template instanceof TemplateReferenceInterface)) {
            throw new \InvalidArgumentException('The template should be a string or implement TemplateReferenceInterface');
        }
        $this->template = $template;

        return $this;
    }

    /**
     * @deprecated since 2.8
     *
     * @param string $templateVar
     *
     * @return View
     */
    public function setTemplateVar($templateVar)
    {
        if (1 === func_num_args() || func_get_arg(1)) {
            @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);
        }

        $this->templateVar = $templateVar;

        return $this;
    }

    /**
     * @deprecated since 2.8
     *
     * @param string $engine
     *
     * @return View
     */
    public function setEngine($engine)
    {
        @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);

        $this->engine = $engine;

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
     * @deprecated since 2.8
     *
     * @return mixed|null
     */
    public function getTemplateData()
    {
        if (0 === func_num_args() || func_get_arg(0)) {
            @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);
        }

        return $this->templateData;
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
     * @deprecated since 2.8
     *
     * @return TemplateReferenceInterface|string|null
     */
    public function getTemplate()
    {
        if (0 === func_num_args() || func_get_arg(0)) {
            @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);
        }

        return $this->template;
    }

    /**
     * @deprecated since 2.8
     *
     * @return string|null
     */
    public function getTemplateVar()
    {
        if (0 === func_num_args() || func_get_arg(0)) {
            @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);
        }

        return $this->templateVar;
    }

    /**
     * @deprecated since 2.8
     *
     * @return string|null
     */
    public function getEngine()
    {
        @trigger_error(sprintf('The %s() method is deprecated since FOSRestBundle 2.8.', __METHOD__), E_USER_DEPRECATED);

        return $this->engine;
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
