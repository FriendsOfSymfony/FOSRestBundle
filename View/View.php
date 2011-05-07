<?php

namespace FOS\RestBundle\View;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference,
    Symfony\Component\Form\FormInterface;

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
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var SerializerInterface
     */
    protected $serializer;

    /**
     * @var array key format, value a callback that returns a Response instance
     */
    protected $customHandlers = array();

    /**
     * @var array key format, value a service id of an EncoderInterface instance
     */
    protected $formats;

    /**
     * @param int HTTP response status code for a failed validation
     */
    protected $failedValidation;

    /**
     * @var array redirect configuration
     */
    protected $redirect;

    /**
     * @var string|TemplateReference template
     */
    protected $template;

    /**
     * @param string format name
     */
    protected $format;

    /**
     * @var string|array parameters
     */
    protected $parameters;

    /**
     * @var string engine (twig, php ..)
     */
    protected $engine;

    /**
     * @param int HTTP response status code
     */
    protected $code;

    /**
     * @var string key that points to a FormInstance inside the parameters
     */
    protected $formKey;

    /**
     * Constructor
     *
     * @param array $formats The supported formats
     * @param int $failedValidation The HTTP response status code for a failed validation
     */
    public function __construct(array $formats = null, $failedValidation = Codes::HTTP_BAD_REQUEST)
    {
        $this->reset();
        $this->formats = (array)$formats;
        $this->failedValidation = $failedValidation;
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
        $this->code = null;
        $this->formKey = null;
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
     * 
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
     * Sets a response HTTP status code for a failed validation
     */
    public function setFailedValidationStatusCode()
    {
        $this->code = $this->failedValidation;
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
     * Sets the key for a FormInstance in the parameters
     *
     * @param string    $key   key that points to a FormInstance inside the parameters
     */
    public function setFormKey($key)
    {
        $this->formKey = $key;
    }

    /**
     * Gets a response HTTP status code
     *
     * By default it will return 200, however for the first form instance in the top level of the parameters it will
     * - set the status code to the failed_validation configuration is the form instance has errors
     * - set inValidFormKey so that the form instance can be replaced with createView() if the format encoder has template support
     *
     * @return int HTTP status code
     */
    private function getStatusCodeFromParameters()
    {
        $parameters = (array)$this->getParameters();

        // Assign the formKey
        if(!$this->formKey){
            foreach ($parameters as $key => $parameter) {
                if ($parameter instanceof FormInterface) {
                    $this->formKey = $key;
                    break;
                }
            }
        }
        
        if(isset($this->formKey)){
            $form = $parameters[$this->formKey];
        }
        
        //Check if the form is valid, return an appropriate response code
        if (isset($form) && $form->isBound() && !$form->isValid()) {
            return $this->failedValidation;
        } else {
            return Codes::HTTP_OK;
        }
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
     * 
     * @throws \InvalidArgumentException if the template is neither a string nor an instance of TemplateReference
     */
    public function setTemplate($template)
    {
        if (!(is_string($template) || $template instanceof TemplateReference)) {
            throw new \InvalidArgumentException('The template should be a string or extend TemplateReference');
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
     * Set the serializer service
     *
     * @param SerializerInterface $serializer a serializer instance
     */
    public function setSerializer(SerializerInterface $serializer = null)
    {
        $this->serializer = $serializer;
    }

    /**
     * Get the serializer service
     *
     * @return SerializerInterface
     */
    public function getSerializer()
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
            $code = $this->getStatusCode();
            if (null === $code) {
                $code = $this->getStatusCodeFromParameters();
            }
            $response = new Response('' , $code);
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
        } else {
            $response = null;
        }

        $this->reset();

        if (!($response instanceof Response)) {
            $response = new Response("Format '$format' not supported, handler must be implemented", Codes::HTTP_UNSUPPORTED_MEDIA_TYPE);
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
            $response->headers->set('Location', $redirect->headers->get('Location'));
            return $response;
        }

        $parameters = $this->getParameters();
        $serializer = $this->getSerializer();
        $encoder = $serializer->getEncoder($format);

        if ($encoder instanceof TemplatingAwareEncoderInterface) {
            $encoder->setTemplate($this->getTemplate());
            if (isset($this->formKey)
                && isset($parameters[$this->formKey])
                && $parameters[$this->formKey] instanceof FormInterface
            ) {
                $parameters[$this->formKey] = $parameters[$this->formKey]->createView();
            }
        }

        $content = $serializer->serialize($parameters, $format);
        $response->setContent($content);

        return $response;
    }
}
