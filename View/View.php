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

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerAwareInterface,
    Symfony\Component\Serializer\SerializerInterface,
    Symfony\Component\Form\FormInterface,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

use FOS\RestBundle\Response\Codes,
    FOS\RestBundle\Serializer\Encoder\TemplatingAwareEncoderInterface;

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
     * @var array the supported formats
     */
    protected $formats;

    /**
     * @param int HTTP response status code for a failed validation
     */
    protected $failedValidation;

    /**
     * @var array target uri
     */
    protected $location;

    /**
     * @var array if to force a redirect for the given key format, with value being the status code to use
     */
    protected $forceRedirects;

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
     * @var string key that points to a FormInstance inside the parameters
     */
    protected $defaultFormKey;

    /**
     * Constructor
     *
     * @param array $formats            The supported formats
     * @param int $failedValidation     The HTTP response status code for a failed validation
     * @param string $defaultFormKey    The default parameter form key
     * @param array $forceRedirects     If to force a redirect for the given key format, with value being the status code to use
     */
    public function __construct(array $formats = null, $failedValidation = Codes::HTTP_BAD_REQUEST, $defaultFormKey = 'form', array $forceRedirects = null)
    {
        $this->formats = (array)$formats;
        $this->failedValidation = $failedValidation;
        $this->defaultFormKey = $defaultFormKey;
        $this->forceRedirects = (array)$forceRedirects;

        $this->reset();
    }

    /**
     * Resets the state of this view instance
     */
    public function reset()
    {
        $this->location = null;
        $this->template = null;
        $this->format = null;
        $this->engine = 'twig';
        $this->parameters = array();
        $this->code = null;
        $this->formKey = $this->defaultFormKey;
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
     * Verifies whether the given format is supported by this view
     *
     * @param string $format format name
     *
     * @return Boolean
     */
    public function supports($format)
    {
        return isset($this->customHandlers[$format]) || in_array($format, $this->formats);
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
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Registered view callback must be callable.');
        }

        $this->customHandlers[$format] = $callback;
    }

    /**
     * Sets a redirect using a route and parameters
     *
     * @param string $route route name
     * @param array $parameters route parameters
     * @param int $code HTTP status code
     */
    public function setResourceRoute($route, array $parameters = array(), $code = Codes::HTTP_CREATED)
    {
        $uri = $this->container->get('router')->generate($route, $parameters, true);
        $this->setRedirectUri($uri, $code);
    }

    /**
     * Sets a redirect using a route and parameters
     *
     * @param string $route route name
     * @param array $parameters route parameters
     * @param int $code HTTP status code
     */
    public function setRedirectRoute($route, array $parameters = array(), $code = Codes::HTTP_FOUND)
    {
        $uri = $this->container->get('router')->generate($route, $parameters, true);
        $this->setRedirectUri($uri, $code);
    }

    /**
     * Sets a redirect using an URI
     *
     * @param string $uri URI
     * @param int $code HTTP status code
     */
    public function setRedirectUri($uri, $code = Codes::HTTP_FOUND)
    {
        $this->setLocation($uri);
        $this->setStatusCode($code);
    }

    /**
     * Sets target location to use when recreating a response
     *
     * @param string $location target uri
     *
     * @throws \InvalidArgumentException if the location is empty
     */
    public function setLocation($location)
    {
        if (empty($location)) {
            throw new \InvalidArgumentException('Cannot redirect to an empty URL.');
        }

        $this->location = $location;
    }

    /**
     * Gets target to use for creating the response
     *
     * @return string target uri
     */
    public function getLocation()
    {
        return $this->location;
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
     * Sets the response HTTP status code for a failed validation
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
        if (false !== $this->formKey) {
            $parameters = $this->getParameters();
            $form = $this->assignFormKey($parameters);
        }

        // Check if the form is valid, return an appropriate response code
        if (isset($form) && $form->isBound() && !$form->isValid()) {
            $this->setFailedValidationStatusCode();
        } else {
            $this->setStatusCode(Codes::HTTP_OK);
        }

        return $this->getStatusCode();
    }

    /**
     * Looks for the form in the $parameters. If no formKey is set
     * but a form is found the formKey is set to the index of the found
     * form.
     *
     * @param FormInterface $parameters
     * @return FormInterface
     */
    protected function assignFormKey($parameters) {
        $form = null;
        if (is_array($parameters)) {
            // Assign the formKey
            if (null === $this->formKey){
                foreach ($parameters as $key => $parameter) {
                    if ($parameter instanceof FormInterface) {
                        $this->setFormKey($key);
                        $form = $parameter;
                        break;
                    }
                }
            } elseif (isset($parameters[$this->formKey])
                && $parameters[$this->formKey] instanceof FormInterface
            ) {
                $form = $parameters[$this->formKey];
            }
        }
        return $form;
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

        $code = $this->getStatusCode();
        if (null === $response) {
            if (null === $code) {
                $code = $this->getStatusCodeFromParameters();
            }
            $response = new Response('' , $code);
        } else {
            $response->setStatusCode($code);
        }

        $format = $this->getFormat();
        if (null === $format) {
            $format = $request->getRequestFormat();
            $this->setFormat($format);
        }

        $response = $this->handleResponse($request, $response, $format);
        $this->reset();

        return $response;
    }

    /**
     * Handle the response base on the format
     *
     * @param Request $request
     * @param Response $response
     * @param string $format
     * @return Response
     */
    protected function handleResponse($request, $response, $format) {
        if (isset($this->customHandlers[$format])) {
            $callback = $this->customHandlers[$format];
            $response = call_user_func($callback, $this, $request, $response);
        } elseif ($this->supports($format)) {
            $response = $this->transform($request, $response, $format);
        } else {
            $response = null;
        }

        if (!($response instanceof Response)) {
            // TODO should we instead set the content/status code on the original response?
            $content = "Format '$format' not supported, handler must be implemented";
            $response = new Response($content, Codes::HTTP_UNSUPPORTED_MEDIA_TYPE);
        }
        return $response;
    }

    /**
     * Generic transformer
     *
     * Handles target and parameter transformation into a response
     *
     * @param Request $request
     * @param Response $response
     * @param string $format
     *
     * @return Response
     */
    protected function transform(Request $request, Response $response, $format)
    {
        $location = $this->getLocation();
        if ($location) {
            if (!empty($this->forceRedirects[$format]) && !$response->isRedirect()) {
                $response->setStatusCode($this->forceRedirects[$format]);
            }

            if ('html' === $format && $response->isRedirect()) {
                // or should RedirectResponse we changed to offer a static method to generate the content?
                $redirect = new \Symfony\Component\HttpFoundation\RedirectResponse($location, $response->getStatusCode());
                $response->setContent($redirect->getContent());
            }

            $response->headers->set('Location', $location);

            return $response;
        }

        $parameters = $this->getParameters();

        $serializer = $this->getSerializer();
        $encoder = $serializer->getEncoder($format);

        if ($encoder instanceof TemplatingAwareEncoderInterface) {
            $encoder->setTemplate($this->getTemplate());
            if (isset($this->formKey)
                && false !== $this->formKey
                && isset($parameters[$this->formKey])
                && $parameters[$this->formKey] instanceof FormInterface
            ) {
                $parameters[$this->formKey] = $parameters[$this->formKey]->createView();
            }
        } else if (isset($this->formKey) && !$parameters[$this->formKey]->isValid()) {
            $children = $parameters[$this->formKey]->getChildren();
            foreach ($children as $key => $child) {
                $children[$key] = $child->getErrors();
            }

            $parameters[$this->formKey] = $children;
        }

        $content = $serializer->serialize($parameters, $format);
        $response->setContent($content);

        return $response;
    }
}
