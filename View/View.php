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

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

/**
 * Default View implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class View
{
    /**
     * @var mixed
     */
    private $data;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var string|TemplateReference
     */
    private $template;

    /**
     * @var string
     */
    private $engine;

    /**
     * @var string
     */
    private $format;

    /**
     * @var string
     */
    private $location;

    /**
     * @var string
     */
    private $route;

    /**
     * Convenience method to allow for a fluent interface.
     *
     * @param mixed $data
     * @param integer $statusCode
     * @param array $headers
     */
    public static function create($data = null, $statusCode = null, array $headers = array())
    {
        return new static($data, $statusCode, $headers);
    }

    /**
     * Constructor
     *
     * @param mixed $data
     * @param integer $statusCode
     * @param array $headers
     */
    public function __construct($data = null, $statusCode = null, array $headers = array())
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    /**
     * set the data
     *
     * @param $data
     * @return View
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * set a header
     *
     * @param $name
     * @param $value
     * @return View
     */
    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    /**
     * set the headers
     *
     * @param array $headers
     * @return View
     */
    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * set the HTTP status code
     *
     * @param $code
     * @return View
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;

        return $this;
    }

    /**
     * Sets template to use for the encoding
     *
     * @param string|TemplateReference $template template to be used in the encoding
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
     * set the engine
     *
     * @param $engine
     * @return View
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;

        return $this;
    }

    /**
     * set the format
     *
     * @param $format
     * @return View
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * set the location (implicitly removes the route)
     *
     * @param $location
     * @return View
     */
    public function setLocation($location)
    {
        $this->location = $location;
        $this->route = null;

        return $this;
    }

    /**
     * set the route (implicitly removes the location)
     *
     * @param $route
     * @return View
     */
    public function setRoute($route)
    {
        $this->route = $route;
        $this->location = null;

        return $this;
    }

    /**
     * get the data
     *
     * @return mixed data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * get the HTTP status code
     *
     * @return int HTTP status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * get the headers
     *
     * @return array headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * get the template
     *
     * @return TemplateReference|string template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * get the engine
     *
     * @return string engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * get the format
     *
     * @return string format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * get the location
     *
     * @return string url
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * get the route
     *
     * @return string route
     */
    public function getRoute()
    {
        return $this->route;
    }
}
