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
    private $templateVar;

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
     * @var string
     */
    private $serializerVersion;

    /**
     *
     * @var array
     */
    private $serializerGroups;

    /**
     *
     * @var array
     */
    private $serializerCallback;

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
        $this->templateVar = 'data';
    }

    /**
     * set the data
     *
     * @param mixed $data
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
     * @param string $name
     * @param string $value
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
     * @param int $code
     * @return View
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;

        return $this;
    }

    /**
     * set the serializer objects version
     *
     * @param string $serializerVersion
     * @return View
     */
    public function setSerializerVersion($serializerVersion)
    {
        $this->serializerVersion = $serializerVersion;
        $this->serializerGroups = null;

        return $this;
    }

    /**
     * set the serializer objects groups
     * @param array $serializerGroups
     * @return View
     */
    public function setSerializerGroups($serializerGroups)
    {
        $this->serializerGroups = $serializerGroups;
        $this->serializerVersion = null;

        return $this;
    }

    /**
     * set the serializer callback
     *
     * function (\FOS\RestBundle\View\ViewHnadler $viewHandler, \JMS\SerializerBundle\Serializer\SerializerInterface $serializer) { .. }
     *
     * @param callable $serializerCallback
     * @return View
     */
    public function setSerializerCallback($serializerCallback)
    {
        $this->serializerCallback = $serializerCallback;

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
     * Sets template variable name to be used in templating formats
     *
     * @param string
     */
    public function setTemplateVar($templateVar)
    {
        $this->templateVar = $templateVar;

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
     * @return mixed|null data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * get the HTTP status code
     *
     * @return int|null HTTP status code
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * get the headers
     *
     * @return array|null headers
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * get the template
     *
     * @return TemplateReference|string|null template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Get the template variable name.
     *
     * @param string|null
     */
    public function getTemplateVar()
    {
        return $this->templateVar;
    }

    /**
     * get the engine
     *
     * @return string|null engine
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * get the format
     *
     * @return string|null format
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * get the location
     *
     * @return string|null url
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * get the route
     *
     * @return string|null route
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * get the serializer version
     *
     * @return string|null serializer version
     */
    public function getSerializerVersion()
    {
        return $this->serializerVersion;
    }

    /**
     * get the serializer groups
     *
     * @return array|null serializer groups
     */
    public function getSerializerGroups()
    {
        return $this->serializerGroups;
    }

    /**
     * get the serializer exclusion strategy
     *
     * @return string|null serializer groups
     */
    public function getSerializerExclusionStrategy()
    {
        if ($this->serializerVersion) {
            return 'version';
        } elseif ($this->serializerGroups) {
            return 'groups';
        }

        return null;
    }

    /**
     * get the serializer callback
     *
     * @return calllable|null serializer callback
     */
    public function getSerializerCallback()
    {
        return $this->serializerCallback;
    }
}
