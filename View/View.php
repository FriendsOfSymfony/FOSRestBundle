<?php

namespace FOS\RestBundle\View;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

use FOS\RestBundle\Response\Codes,
    FOS\RestBundle\Serializer\Encoder\TemplatingAwareEncoderInterface;

/*
 * This file is part of the FOSRestBundle
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

    protected $redirect;
    protected $template;
    protected $format;
    protected $parameters;
    protected $engine;
    protected $code;

    /**
     * Constructor
     *
     * @param array $formats The supported formats
     */
    public function __construct(array $formats = null)
    {
        $this->reset();
        $this->formats = (array)$formats;
    }

    /**
     * Resets the state of this view instance
     */
    public function reset()
    {
        $this->redirect = null;
        $this->template = null;
        $this->format = null;
        $this->engine = 'twig';
        $this->parameters = array();
        $this->code = Codes::HTTP_OK;
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
     * @return Boolean
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
     * @param int $code HTTP status code
     */
    public function setRouteRedirect($route, array $parameters = array(), $code = Codes::HTTP_FOUND)
    {
        $this->redirect = array(
            'route' => $route,
            'parameters' => $parameters,
            'status_code' => $code,
        );
    }

    /**
     * Sets a redirect using an URI
     *
     * @param string $uri URI
     * @param int $code HTTP status code
     */
    public function setUriRedirect($uri, $code = Codes::HTTP_FOUND)
    {
        $this->redirect = array('location' => $uri, 'status_code' => $code);
    }

    /**
     * Sets a response HTTP status code
     *
     * @param int $code optional http status code
     */
    public function setStatusCode($code)
    {
        $this->code = $code;
    }

    /**
     * Gets a response HTTP status code
     *
     * @return int HTTP status code
     */
    public function getStatusCode()
    {
        return $this->code;
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
     * Sets to be encoded parameters
     *
     * @param string|array $parameters parameters to be used in the encoding
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * Gets to be encoded parameters
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
     * @param string|TemplateReference $template template to be used in the encoding
     */
    public function setTemplate($template)
    {
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
     * Set the serializer service
     *
     * @param SerializerInterface $serializer a serializer instance
     */
    public function setSerializer(SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer;
    }

    /**
     * Get the serializer service, add encoder in case there is none set for the given format
     *
     * @param string $format
     *
     * @return SerializerInterface
     */
    public function getSerializer($format = null)
    {
        if (null === $this->serializer) {
            $this->serializer = $this->container->get('fos_rest.serializer');
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
     * @return Response
     */
    public function handle(Request $request = null, Response $response = null)
    {
        if (null === $request) {
            $request = $this->container->get('request');
        }

        if (null === $response) {
            $response = new Response();
        } else {
            $this->setStatusCode($response->getStatusCode());
        }

        $format = $this->getFormat();
        if (null === $format) {
            $format = $request->getRequestFormat();
            $this->setFormat($format);
        }

        if (isset($this->customHandlers[$format])) {
            $callback = $this->customHandlers[$format];
            $response = call_user_func($callback, $this, $request, $response);
        } elseif ($this->supports($format)) {
            $response = $this->transform($request, $response, $format);
        }

        $this->reset();

        if (!($response instanceof Response)) {
            $response = new Response("Format '$format' not supported, handler must be implemented", Codes::HTTP_UNSUPPORTED_MEDIA_TYPE);
        } else {
            $response->setStatusCode($this->getStatusCode());
        }

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
     *
     * @return Response
     */
    protected function transform(Request $request, Response $response, $format)
    {
        if ($this->redirect) {
            // TODO add support to optionally return the target url
            if (empty($this->redirect['location'])) {
                // TODO add support to optionally forward to the route
                $this->redirect['location'] = $this->container->get('router')->generate($this->redirect['route'], $this->redirect['parameters']);
            }
            $redirect = new RedirectResponse($this->redirect['location'], $this->redirect['status_code']);
            $response->setContent($redirect->getContent());
            $this->setStatusCode($this->redirect['status_code']);
            $response->headers->set('Location', $redirect->headers->get('Location'));
            return $response;
        }

        $serializer = $this->getSerializer($format);
        $encoder = $serializer->getEncoder($format);

        if ($encoder instanceof TemplatingAwareEncoderInterface) {
            $encoder->setTemplate($this->getTemplate());
        }

        $content = $serializer->serialize($this->getParameters(), $format);
        $response->setContent($content);

        return $response;
    }
}
