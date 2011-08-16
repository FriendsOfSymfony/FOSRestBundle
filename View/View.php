<?php

namespace FOS\RestBundle\View;

/**
 * Default View implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
use Symfony\Component\Form\FormInterface,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

class View
{
    private $parameters;
    private $statusCode;
    private $headers;
    private $template;
    private $engine = 'twig';
    private $format;
    private $location;

    /**
     * Convenience method to allow for a fluent interface.
     *
     * @param mixed $parameters
     * @param integer $statusCode
     * @param array $headers
     */
    public static function create($parameters = array(), $statusCode = null, array $headers = array())
    {
        return new self($parameters, $statusCode, $headers);
    }

    public function __construct($parameters = array(), $statusCode = null, array $headers = array())
    {
        $this->parameters = $parameters;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;

        return $this;
    }

    public function setHeaders(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

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

    public function setEngine($engine)
    {
        $this->engine = $engine;

        return $this;
    }

    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function getStatusCode()
    {
        return $this->statusCode;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function getEngine()
    {
        return $this->engine;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getLocation()
    {
        return $this->location;
    }
}
