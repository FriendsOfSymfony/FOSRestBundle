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

use JMS\Serializer\SerializerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Form\FormInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use FOS\RestBundle\Util\Codes;

/**
 * View may be used in controllers to build up a response in a format agnostic way
 * The View class takes care of encoding your data in json, xml, or renders a
 * template for html via the Serializer component.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class ViewHandler implements ConfigurableViewHandlerInterface, ContainerAwareInterface
{
    /**
     * Key format, value a callable that returns a Response instance.
     *
     * @var array
     */
    protected $customHandlers = array();

    /**
     * The supported formats as keys and if the given formats
     * uses templating is denoted by a true value.
     *
     * @var array
     */
    protected $formats;

    /**
     *  HTTP response status code for a failed validation.
     *
     * @var int
     */
    protected $failedValidationCode;

    /**
     * HTTP response status code when the view data is null.
     *
     * @var int
     */
    protected $emptyContentCode;

    /**
     * Whether or not to serialize null view data.
     *
     * @var bool
     */
    protected $serializeNull;

    /**
     * If to force a redirect for the given key format,
     * with value being the status code to use.
     *
     * @var array
     */
    protected $forceRedirects;

    /**
     * @var string
     */
    protected $defaultEngine;

    /**
     * @var array
     */
    protected $exclusionStrategyGroups = array();

    /**
     * @var string
     */
    protected $exclusionStrategyVersion;

    /**
     * @var bool
     */
    protected $serializeNullStrategy;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Constructor.
     *
     * @param array  $formats              the supported formats as keys and if the given formats uses templating is denoted by a true value
     * @param int    $failedValidationCode The HTTP response status code for a failed validation
     * @param int    $emptyContentCode     HTTP response status code when the view data is null
     * @param bool   $serializeNull        Whether or not to serialize null view data
     * @param array  $forceRedirects       If to force a redirect for the given key format, with value being the status code to use
     * @param string $defaultEngine        default engine (twig, php ..)
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
     * Sets the Container associated with this Controller.
     *
     * @param ContainerInterface $container A ContainerInterface instance
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Sets the default serialization groups.
     *
     * @param array|string $groups
     */
    public function setExclusionStrategyGroups($groups)
    {
        $this->exclusionStrategyGroups = $groups;
    }

    /**
     * Sets the default serialization version.
     *
     * @param string $version
     */
    public function setExclusionStrategyVersion($version)
    {
        $this->exclusionStrategyVersion = $version;
    }

    /**
     * If nulls should be serialized.
     *
     * @param bool $isEnabled
     */
    public function setSerializeNullStrategy($isEnabled)
    {
        $this->serializeNullStrategy = $isEnabled;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($format)
    {
        return isset($this->customHandlers[$format]) || isset($this->formats[$format]);
    }

    /**
     * Registers a custom handler.
     *
     * The handler must have the following signature: handler(ViewHandler $viewHandler, View $view, Request $request, $format)
     * It can use the public methods of this class to retrieve the needed data and return a
     * Response object ready to be sent.
     *
     * @param string   $format
     * @param callable $callable
     *
     * @throws \InvalidArgumentException
     */
    public function registerHandler($format, $callable)
    {
        if (!is_callable($callable)) {
            throw new \InvalidArgumentException('Registered view callback must be callable.');
        }

        $this->customHandlers[$format] = $callable;
    }

    /**
     * Gets a response HTTP status code from a View instance.
     *
     * By default it will return 200. However if there is a FormInterface stored for
     * the key 'form' in the View's data it will return the failed_validation
     * configuration if the form instance has errors.
     *
     * @param View  $view
     * @param mixed $content
     *
     * @return int HTTP status code
     */
    protected function getStatusCode(View $view, $content = null)
    {
        $form = $this->getFormFromView($view);

        if ($form && $form->isSubmitted() && !$form->isValid()) {
            return $this->failedValidationCode;
        }

        if (200 !== ($code = $view->getStatusCode())) {
            return $code;
        }

        return null !== $content ? Codes::HTTP_OK : $this->emptyContentCode;
    }

    /**
     * If the given format uses the templating system for rendering.
     *
     * @param string $format
     *
     * @return bool
     */
    public function isFormatTemplating($format)
    {
        return !empty($this->formats[$format]);
    }

    /**
     * Gets the router service.
     *
     * @return \Symfony\Component\Routing\RouterInterface
     */
    protected function getRouter()
    {
        return $this->container->get('fos_rest.router');
    }

    /**
     * Gets the serializer service.
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
     * the view exclusion strategies, groups & versions if a new context is created.
     *
     * @param View $view
     *
     * @return SerializationContext
     */
    protected function getSerializationContext(View $view)
    {
        $context = $view->getSerializationContext();

        if ($context->attributes->get('groups')->isEmpty() && $this->exclusionStrategyGroups) {
            $context->setGroups($this->exclusionStrategyGroups);
        }

        if ($context->attributes->get('version')->isEmpty() && $this->exclusionStrategyVersion) {
            $context->setVersion($this->exclusionStrategyVersion);
        }

        if (null === $context->shouldSerializeNull() && null !== $this->serializeNullStrategy) {
            $context->setSerializeNull($this->serializeNullStrategy);
        }

        return $context;
    }

    /**
     * Gets the templating service.
     *
     * @return \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface
     */
    protected function getTemplating()
    {
        return $this->container->get('fos_rest.templating');
    }

    /**
     * Handles a request with the proper handler.
     *
     * Decides on which handler to use based on the request format.
     *
     * @param View    $view
     * @param Request $request
     *
     * @return Response
     *
     * @throws UnsupportedMediaTypeHttpException
     */
    public function handle(View $view, Request $request = null)
    {
        if (null === $request) {
            $request = $this->container->has('request_stack')
                ? $this->container->get('request_stack')->getCurrentRequest()
                : $this->container->get('request');
        }

        $format = $view->getFormat() ?: $request->getRequestFormat();

        if (!$this->supports($format)) {
            $msg = "Format '$format' not supported, handler must be implemented";
            throw new UnsupportedMediaTypeHttpException($msg);
        }

        if (isset($this->customHandlers[$format])) {
            return call_user_func($this->customHandlers[$format], $this, $view, $request, $format);
        }

        return $this->createResponse($view, $request, $format);
    }

    /**
     * Creates the Response from the view.
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
        if (($view->getStatusCode() == Codes::HTTP_CREATED || $view->getStatusCode() == Codes::HTTP_ACCEPTED) && $view->getData() != null) {
            $response = $this->initResponse($view, $format);
        } else {
            $response = $view->getResponse();
            if ('html' === $format && isset($this->forceRedirects[$format])) {
                $redirect = new RedirectResponse($location);
                $content = $redirect->getContent();
                $response->setContent($content);
            }
        }

        $code = isset($this->forceRedirects[$format])
            ? $this->forceRedirects[$format] : $this->getStatusCode($view, $content);

        $response->setStatusCode($code);
        $response->headers->set('Location', $location);

        return $response;
    }

    /**
     * Renders the view data with the given template.
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
        if ($template instanceof TemplateReference) {
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
     * Prepares view data for use by templating engine.
     *
     * @param View $view
     *
     * @return array
     */
    public function prepareTemplateParameters(View $view)
    {
        $data = $view->getData();

        if ($data instanceof FormInterface) {
            $data = array($view->getTemplateVar() => $data->getData(), 'form' => $data);
        } elseif (empty($data) || !is_array($data) || is_numeric((key($data)))) {
            $data = array($view->getTemplateVar() => $data);
        }

        if (isset($data['form']) && $data['form'] instanceof FormInterface) {
            $data['form'] = $data['form']->createView();
        }

        $templateData = $view->getTemplateData();
        if (is_callable($templateData)) {
            $templateData = call_user_func($templateData, $this, $view);
        }

        return array_merge($data, $templateData);
    }

    /**
     * Handles creation of a Response using either redirection or the templating/serializer service.
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
            ? $this->getRouter()->generate($route, (array) $view->getRouteParameters(), true)
            : $view->getLocation();

        if ($location) {
            return $this->createRedirectResponse($view, $location, $format);
        }

        $response = $this->initResponse($view, $format);

        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', $request->getMimeType($format));
        }

        return $response;
    }

    /**
     * Initializes a response object that represents the view and holds the view's status code.
     *
     * @param View   $view
     * @param string $format
     *
     * @return Response
     */
    private function initResponse(View $view, $format)
    {
        $content = null;
        if ($this->isFormatTemplating($format)) {
            $content = $this->renderTemplate($view, $format);
        } elseif ($this->serializeNull || null !== $view->getData()) {
            $data = $this->getDataFromView($view);
            $serializer = $this->getSerializer($view);
            if ($serializer instanceof SerializerInterface) {
                $context = $this->getSerializationContext($view);
                $content = $serializer->serialize($data, $format, $context);
            } else {
                $content = $serializer->serialize($data, $format);
            }
        }

        $response = $view->getResponse();
        $response->setStatusCode($this->getStatusCode($view, $content));

        if (null !== $content) {
            $response->setContent($content);
        }

        return $response;
    }

    /**
     * Returns the form from the given view if present, false otherwise.
     *
     * @param View $view
     *
     * @return bool|FormInterface
     */
    protected function getFormFromView(View $view)
    {
        $data = $view->getData();

        if ($data instanceof FormInterface) {
            return $data;
        }

        if (is_array($data) && isset($data['form']) && $data['form'] instanceof FormInterface) {
            return $data['form'];
        }

        return false;
    }

    /**
     * Returns the data from a view. If the data is form with errors, it will return it wrapped in an ExceptionWrapper.
     *
     * @param View $view
     *
     * @return mixed|null
     */
    private function getDataFromView(View $view)
    {
        $form = $this->getFormFromView($view);

        if (false === $form) {
            return $view->getData();
        }

        if ($form->isValid() || !$form->isSubmitted()) {
            return $form;
        }

        /** @var ExceptionWrapperHandlerInterface $exceptionWrapperHandler */
        $exceptionWrapperHandler = $this->container->get('fos_rest.exception_handler');

        return $exceptionWrapperHandler->wrap(
            array(
                 'status_code' => $this->failedValidationCode,
                 'message' => 'Validation Failed',
                 'errors' => $form,
            )
        );
    }
}
