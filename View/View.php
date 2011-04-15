<?php

namespace FOS\RestBundle\View;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

use FOS\RestBundle\Serializer\Encoder\TemplatingAwareEncoderInterface;

/*
 * This file is part of the FOS/RestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * View may be used in controllers to build up a response in a format agnostic way
 * The View class takes care of encoding your data in json, xml, or renders a
 * template for html via the Serializer component.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class View implements ContainerAwareInterface
{
    protected $container;
    protected $serializer;

    protected $customHandlers = array();
    protected $formats;
    protected $useAcceptHeaders;
    protected $defaultFormat;

    protected $redirect;
    protected $template;
    protected $format;
    protected $parameters;
    protected $engine;

    /**
     * Constructor
     *
     * @param array $formats The supported formats
     * @param boolean $useAcceptHeaders If Accept headers should be read
     * @param string $defaultFormat The default format
     */
    public function __construct(array $formats = null, $useAcceptHeaders = null, $defaultFormat = null)
    {
        $this->reset();
        $this->formats = (array)$formats;
        $this->useAcceptHeaders = $useAcceptHeaders;
        $this->defaultFormat = $defaultFormat;
    }

    /**
     * Resets the state of the view object
     */
    public function reset()
    {
        $this->redirect = null;
        $this->template = null;
        $this->format = null;
        $this->engine = 'twig';
        $this->parameters = array();
    }

    /**
     * Reset serializer service
     */
    public function resetSerializer()
    {
        $this->serializer = null;
    }

    /**
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Sets what formats are supported
     *
     * @param array $formats list of supported formats
     */
    public function setFormats($formats)
    {
        $this->formats = array_replace($this->formats, $formats);
    }

    /**
     * Verifies whether the given format is supported by this view
     *
     * @param string $format format name
     * @return bool
     */
    public function supports($format)
    {
        return isset($this->customHandlers[$format]) || !empty($this->formats[$format]);
    }

    /**
     * Registers a custom handler
     *
     * The handler must have the following signature: handler($viewObject, $request, $response)
     * It can use the public methods of this class to retrieve the needed data and return a
     * Response object ready to be sent.
     *
     * @param string $format the format that is handled
     * @param callback $callback handler callback
     */
    public function registerHandler($format, $callback)
    {
        $this->customHandlers[$format] = $callback;
    }

    /**
     * Sets a redirect using a route and parameters
     *
     * @param string $route route name
     * @param array $parameters route parameters
     * @param int $code optional http status code
     */
    public function setRouteRedirect($route, array $parameters = array(), $code = 302)
    {
        $this->redirect = array(
            'location' => $this->container->get('router')->generate($route, $parameters),
            'status_code' => $code,
        );
    }

    /**
     * Sets a redirect using an URI
     *
     * @param string $uri URI
     * @param int $code optional http status code
     */
    public function setUriRedirect($uri, $code = 302)
    {
        $this->redirect = array('location' => $uri, 'status_code' => $code);
    }

    /**
     * Gets a redirect
     *
     * @return array redirect location and status code
     */
    public function getRedirect()
    {
        return $this->redirect;
    }

    /**
     * Sets encoding parameters
     *
     * @param string|array $parameters parameters to be used in the encoding
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Gets encoding parameters
     *
     * @return string|array parameters to be used in the encoding
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Sets template to use for the encoding
     *
     * @param string|array|TemplateReference $template template to be used in the encoding
     */
    public function setTemplate($template)
    {
        if (is_array($template)) {
            if (empty($template['name'])) {
                throw new \InvalidArgumentException('The "name" key must be set: '.serialize($template));
            }

            $bundle = empty($template['bundle']) ? null : $template['bundle'];
            $controller = empty($template['controller']) ? null : $template['controller'];
            $format = empty($template['format']) ? null : $template['format'];
            $engine = empty($template['engine']) ? null : $template['engine'];

            $template = new TemplateReference($bundle, $controller, $template['name'], $format, $engine);
        }

        $this->template = $template;
    }

    /**
     * Gets template to use for the encoding
     *
     * When the template is an array this method
     * ensures that the format and engine are set
     *
     * @return string|TemplateReference template to be used in the encoding
     */
    public function getTemplate()
    {
        $template = $this->template;

        if ($template instanceOf TemplateReference) {
            if (null === $template->get('format')) {
                $template->set('format', $this->getFormat());
            }

            if (null === $template->get('engine')) {
                $template->set('engine', $this->getEngine());
            }
        }

        return $template;
    }

    /**
     * Sets engine to use for the encoding
     *
     * @param string $engine engine to be used in the encoding
     */
    public function setEngine($engine)
    {
        $this->engine = $engine;
    }

    /**
     * Gets engine to use for the encoding
     *
     * @return string engine to be used in the encoding
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * Sets encoding format
     *
     * @param string $format format to be used in the encoding
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * Gets encoding format
     *
     * @return string format to be used in the encoding
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Detect encoding format
     *
     * @param Request $request The request
     *
     * @return string format to be used in the encoding
     */
    protected function detectFormat($request)
    {
        // TODO: add ability to define a format negotiation service and/or closure
        $format = $request->getRequestFormat(null);
        if (null === $format) {
            if ($this->useAcceptHeaders) {
                $formats = $this->splitHttpAcceptHeader($request->headers->get('Accept'));
                if (!empty($formats)) {
                    $format = $request->getFormat(key($formats));
                }

                if (null === $format) {
                    $format = $this->defaultFormat;
                }
            } else {
                $format = $this->defaultFormat;
            }
        }

        $this->setFormat($format);

        return $format;
    }

    /**
     * Splits an Accept-* HTTP header.
     * TODO remove once https://github.com/symfony/symfony/pull/565 is merged
     *
     * @param string $header  Header to split
     */
    public function splitHttpAcceptHeader($header)
    {
        if (!$header) {
            return array();
        }

        $values = array();
        foreach (array_filter(explode(',', $header)) as $value) {
            // Cut off any q-value that might come after a semi-colon
            if ($pos = strpos($value, ';')) {
                $q     = (float) trim(substr($value, strpos($value, '=') + 1));
                $value = trim(substr($value, 0, $pos));
            } else {
                $q = 1;
            }

            if (0 < $q) {
                $values[trim($value)] = $q;
            }
        }

        arsort($values);
        reset($values);

        return $values;
    }

    /**
     * Set the serializer service
     *
     * @param Symfony\Component\Serializer\SerializerInterface $serializer a serializer instance
     */
    public function setSerializer($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * Get the serializer service, add encoder in case there is none set for the given format
     *
     * @param string $format
     *
     * @return Symfony\Component\Serializer\SerializerInterface
     */
    public function getSerializer($format = null)
    {
        if (null === $this->serializer) {
            $this->serializer = $this->container->get('fos_rest.serializer');
        }

        if (null !== $format && !$this->serializer->hasEncoder($format)) {
            $this->serializer->setEncoder($format, $this->container->get($this->formats[$format]));
        }

        return $this->serializer;
    }

    /**
     * Handles a request with the proper handler
     *
     * Decides on which handler to use based on the request format
     *
     * @param Request $request Request object
     * @param Response $response optional response object to use
     *
     * @param Response
     */
    public function handle(Request $request = null, Response $response = null)
    {
        if (null === $request) {
            $request = $this->container->get('request');
        }

        if (null === $response) {
            $response = new Response();
        }

        $format = $this->getFormat();
        if (null === $format) {
            $format = $this->detectFormat($request);
        }

        if (isset($this->customHandlers[$format])) {
            $callback = $this->customHandlers[$format];
            $response = call_user_func($callback, $this, $request, $response);
        } else {
            if (!$this->supports($format)) {
                return new Response("Format '$format' not supported, handler must be implemented", 415);
            }
            $response = $this->transform($request, $response, $format, $this->getTemplate());
        }

        $this->reset();

        return $response;
    }

    /**
     * Generic transformer
     *
     * Handles redirects, or transforms the parameters into a response content
     *
     * @param Request $request
     * @param Response $response
     * @param string $format
     * @param string $template
     *
     * @return Response
     */
    protected function transform(Request $request, Response $response, $format, $template)
    {
        if ($this->redirect) {
            $redirect = new RedirectResponse($this->redirect['location'], $this->redirect['status_code']);
            $response->setContent($redirect->getContent());
            $response->setStatusCode($this->redirect['status_code']);
            $response->headers->set('Location', $redirect->headers->get('Location'));
            return $response;
        }

        $serializer = $this->getSerializer($format);
        $encoder = $serializer->getEncoder($format);

        if ($encoder instanceof TemplatingAwareEncoderInterface) {
            $encoder->setTemplate($template);
        }

        $content = $serializer->serialize($this->getParameters(), $format);

        $response->setContent($content);
        return $response;
    }
}
