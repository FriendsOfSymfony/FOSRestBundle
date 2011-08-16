<?php

namespace FOS\RestBundle\View;

/**
 * Default View implementation.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
use Symfony\Component\Form\FormInterface;

class View
{
    private $data;
    private $statusCode;
    private $headers;
    private $template;
    private $engine;
    private $form;
    private $format;
    private $location;

    /**
     * Convenience method to allow for a fluent interface.
     *
     * @param mixed $data
     * @param integer $statusCode
     * @param array $headers
     */
    public static function create($data = null, $statusCode = null, array $headers = array())
    {
        return new self($data, $statusCode, $headers);
    }

    public function __construct($data = null, $statusCode = null, array $headers = array())
    {
        $this->data = $data;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public function setData($data)
    {
        $this->data = $data;

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

    public function setForm(FormInterface $form)
    {
        $this->form = $form;

        return $this;
    }

    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    public function getData()
    {
        return $this->data;
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

    public function getForm()
    {
        return $this->form;
    }

    public function getFormat()
    {
        return $this->format;
    }
}