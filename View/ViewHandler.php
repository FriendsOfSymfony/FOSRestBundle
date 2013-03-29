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

use JMS\Serializer\Serializer;
use JMS\Serializer\SerializationContext;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

use FOS\Rest\Util\Codes;

/**
 * View may be used in controllers to build up a response in a format agnostic way
 * The View class takes care of encoding your data in json, xml, or renders a
 * template for html via the Serializer component.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class ViewHandler extends ContainerAware implements ViewHandlerInterface
{
    /**
     * @var array key format, value a callable that returns a Response instance
     */
    protected $customHandlers = array();

    /**
     * @var array the supported formats as keys and if the given formats uses templating is denoted by a true value
     */
    protected $formats;

    /**
     * @param int HTTP response status code for a failed validation
     */
    protected $failedValidationCode;

    /**
     * @param int HTTP response status code when the view data is null
     */
    protected $emptyContentCode;

    /**
     * @param int Whether or not to serialize null view data
     */
    protected $serializeNull;

    /**
     * @var array if to force a redirect for the given key format, with value being the status code to use
     */
    protected $forceRedirects;

    /**
     * @var string default engine (twig, php ..)
     */
    protected $defaultEngine;

    /**
     * Constructor
     *
     * @param array   $formats              the supported formats as keys and if the given formats uses templating is denoted by a true value
     * @param int     $failedValidationCode The HTTP response status code for a failed validation
     * @param int     $emptyContentCode     HTTP response status code when the view data is null
     * @param Boolean $serializeNull        Whether or not to serialize null view data
     * @param array   $forceRedirects       If to force a redirect for the given key format, with value being the status code to use
     * @param string  $defaultEngine        default engine (twig, php ..)
     */
    public function __construct(
        array $formats = null,
        $failedValidationCode = Codes::HTTP_BAD_REQUEST,
        $emptyContentCode = Codes::HTTP_NO_CONTENT,
        $serializeNull = false,
        array $forceRedirects = null,
        $defaultEngine = 'twig'
    ) {
        $this->formats = (array) $formats;
        $this->failedValidationCode = $failedValidationCode;
        $this->emptyContentCode = $emptyContentCode;
        $this->serializeNull = $serializeNull;
        $this->forceRedirects = (array) $forceRedirects;
        $this->defaultEngine = $defaultEngine;
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
        return isset($this->customHandlers[$format]) || isset($this->formats[$format]);
    }

    /**
     * Registers a custom handler
     *
     * The handler must have the following signature: handler(ViewHandler $viewHandler, View $view, Request $request, $format)
     * It can use the public methods of this class to retrieve the needed data and return a
     * Response object ready to be sent.
     *
     * @param string   $format   the format that is handled
     * @param callable $callable callable that can handle the given format
     */
    public function registerHandler($format, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Registered view callback must be callable.');
        }

        $this->customHandlers[$format] = $callable;
    }

    /**
     * Gets a response HTTP status code from a View instance
     *
     * By default it will return 200. However if there is a FormInterface stored for
     * the key 'form' in the View's data it will return the failed_validation
     * configuration if the form instance has errors.
     *
     * @param View $view view instance
     * @param mixed $content
     *
     * @return int HTTP status code
     */
    protected function getStatusCode(View $view, $content = null)
    {
        if (200 !== ($code = $view->getStatusCode())) {
            return $code;
        }

        $data = $view->getData();
        if ($data instanceof FormInterface) {
            $form = $data;
        } elseif (is_array($data) && isset($data['form'])  && $data['form'] instanceof FormInterface) {
            $form = $data['form'];
        } else {
            $form = false;
        }

        if ($form && $form->isBound() && !$form->isValid()) {
            return $this->failedValidationCode;
        }

        return null !== $content ? Codes::HTTP_OK : $this->emptyContentCode;
    }

    /**
     * If the given format uses the templating system for rendering
     *
     * @param string $format
     *
     * @return Boolean
     */
    public function isFormatTemplating($format)
    {
        return !empty($this->formats[$format]);
    }

    /**
     * Get the router service
     *
     * @return Symfony\Component\Routing\RouterInterface
     */
    protected function getRouter()
    {
        return $this->container->get('fos_rest.router');
    }

    /**
     * Get the serializer service
     *
     * @param View $view view instance from which the serializer should be configured
     *
     * @return object that must provide a "serialize()" method
     */
    protected function getSerializer(View $view = null)
    {
        return $this->container->get('fos_rest.serializer');
    }

    /**
     * Gets or creates a JMS\Serializer\SerializationContext and initializes it with
     * the view exclusion strategies, groups & versions if a new context is created
     *
     * @param View $view
     *
     * @return SerializationContext
     */
    public function getSerializationContext(View $view)
    {
        $context = $view->getSerializationContext();
        if (null === $context) {
            $context = new SerializationContext();

            $groups = $this->container->getParameter('fos_rest.serializer.exclusion_strategy.groups');
            if ($groups) {
                $context->setGroups($groups);
            }

            $version = $this->container->getParameter('fos_rest.serializer.exclusion_strategy.version');
            if ($version) {
                $context->setVersion($version);
            }
        }

        return $context;
    }

    /**
     * Get the templating service
     *
     * @return Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    protected function getTemplating()
    {
        return $this->container->get('fos_rest.templating');
    }

    /**
     * Handles a request with the proper handler
     *
     * Decides on which handler to use based on the request format
     *
     * @param View    $view
     * @param Request $request Request object
     *
     * @return Response
     */
    public function handle(View $view, Request $request = null)
    {
        if (null === $request) {
            $request = $this->container->get('request');
        }

        $format = $view->getFormat() ?: $request->getRequestFormat();

        if (!$this->supports($format)) {
            $msg = "Format '$format' not supported, handler must be implemented";
            throw new HttpException(Codes::HTTP_UNSUPPORTED_MEDIA_TYPE, $msg);
        }

        if (isset($this->customHandlers[$format])) {
            return call_user_func($this->customHandlers[$format], $this, $view, $request, $format);
        }

        return $this->createResponse($view, $request, $format);
    }

    /**
     * Create the Response from the view
     *
     * @param View   $view
     * @param string $location
     * @param string $format
     *
     * @return Response
     */
    public function createRedirectResponse(View $view, $location, $format)
    {
        $content = null;
        $response = $view->getResponse();
        if ('html' === $format && isset($this->forceRedirects[$format])) {
            $redirect = new RedirectResponse($location);
            $content = $redirect->getContent();
            $response->setContent($content);
        }

        $code = isset($this->forceRedirects[$format])
            ? $this->forceRedirects[$format] : $this->getStatusCode($view, $content);

        $response->setStatusCode($code);
        $response->headers->set('Location', $location);
        return $response;
    }

    /**
     * Render the view data with the given template
     *
     * @param View   $view
     * @param string $format
     *
     * @return string
     */
    public function renderTemplate(View $view, $format)
    {
        $data = $this->prepareTemplateParameters($view);

        $template = $view->getTemplate();
        if ($template instanceOf TemplateReference) {
            if (null === $template->get('format')) {
                $template->set('format', $format);
            }

            if (null === $template->get('engine')) {
                $engine = $view->getEngine() ?: $this->defaultEngine;
                $template->set('engine', $engine);
            }
        }

        return $this->getTemplating()->render($template, $data);
    }

    /**
     * Prepare view data for use by templating engine.
     *
     * @param View $view
     *
     * @return array
     */
    public function prepareTemplateParameters(View $view)
    {
        $data = $view->getData();
        if ($data instanceof FormInterface) {
            return array($view->getTemplateVar() => $data->getData(), 'form' => $data->createView());
        }

        if (empty($data) || !is_array($data) || is_numeric((key($data)))) {
            return array($view->getTemplateVar() => $data);
        }

        if (isset($data['form']) && $data['form'] instanceof FormInterface) {
            $data['form'] = $data['form']->createView();
        }

        return $data;
    }

    /**
     * Handles creation of a Response using either redirection or the templating/serializer service
     *
     * @param View    $view
     * @param Request $request
     * @param string  $format
     *
     * @return Response
     */
    public function createResponse(View $view, Request $request, $format)
    {
        $route = $view->getRoute();
        $location = $route
            ? $this->getRouter()->generate($route, (array) $view->getData(), true)
            : $view->getLocation();

        if ($location) {
            return $this->createRedirectResponse($view, $location, $format);
        }

        $content = null;
        if ($this->isFormatTemplating($format)) {
            $content = $this->renderTemplate($view, $format);
        } elseif ($this->serializeNull || null !== $view->getData()) {
            $serializer = $this->getSerializer($view);
            if ($serializer instanceof Serializer) {
                $context = $this->getSerializationContext($view);
                $content = $serializer->serialize($view->getData(), $format, $context);
            } else {
                $content = $serializer->serialize($view->getData(), $format);
            }
        }

        $response = $view->getResponse();
        $response->setStatusCode($this->getStatusCode($view, $content));

        if (null !== $content) {
            $response->setContent($content);
        }

        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', $request->getMimeType($format));
        }

        return $response;
    }
}
