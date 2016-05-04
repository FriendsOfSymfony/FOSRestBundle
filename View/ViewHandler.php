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

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Templating\TemplateReferenceInterface;

/**
 * View may be used in controllers to build up a response in a format agnostic way
 * The View class takes care of encoding your data in json, xml, or renders a
 * template for html via the Serializer component.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class ViewHandler implements ConfigurableViewHandlerInterface
{
    /**
     * Key format, value a callable that returns a Response instance.
     *
     * @var array
     */
    protected $customHandlers = [];

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
    protected $exclusionStrategyGroups = [];

    /**
     * @var string
     */
    protected $exclusionStrategyVersion;

    /**
     * @var bool
     */
    protected $serializeNullStrategy;

    private $urlGenerator;
    private $serializer;
    private $templating;
    private $requestStack;

    /**
     * Constructor.
     *
     * @param UrlGeneratorInterface $urlGenerator         The URL generator
     * @param Serializer            $serializer
     * @param EngineInterface       $templating           The configured templating engine
     * @param RequestStack          $requestStack         The request stack
     * @param array                 $formats              the supported formats as keys and if the given formats uses templating is denoted by a true value
     * @param int                   $failedValidationCode The HTTP response status code for a failed validation
     * @param int                   $emptyContentCode     HTTP response status code when the view data is null
     * @param bool                  $serializeNull        Whether or not to serialize null view data
     * @param array                 $forceRedirects       If to force a redirect for the given key format, with value being the status code to use
     * @param string                $defaultEngine        default engine (twig, php ..)
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        Serializer $serializer,
        EngineInterface $templating,
        RequestStack $requestStack,
        array $formats = null,
        $failedValidationCode = Response::HTTP_BAD_REQUEST,
        $emptyContentCode = Response::HTTP_NO_CONTENT,
        $serializeNull = false,
        array $forceRedirects = null,
        $defaultEngine = 'twig'
    ) {
        $this->urlGenerator = $urlGenerator;
        $this->serializer = $serializer;
        $this->templating = $templating;
        $this->requestStack = $requestStack;
        $this->formats = (array) $formats;
        $this->failedValidationCode = $failedValidationCode;
        $this->emptyContentCode = $emptyContentCode;
        $this->serializeNull = $serializeNull;
        $this->forceRedirects = (array) $forceRedirects;
        $this->defaultEngine = $defaultEngine;
    }

    /**
     * Sets the default serialization groups.
     *
     * @param array|string $groups
     */
    public function setExclusionStrategyGroups($groups)
    {
        $this->exclusionStrategyGroups = (array) $groups;
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

        $statusCode = $view->getStatusCode();
        if (null !== $statusCode) {
            return $statusCode;
        }

        return null !== $content ? Response::HTTP_OK : $this->emptyContentCode;
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
     * Gets or creates a JMS\Serializer\SerializationContext and initializes it with
     * the view exclusion strategies, groups & versions if a new context is created.
     *
     * @param View $view
     *
     * @return Context
     */
    protected function getSerializationContext(View $view)
    {
        $context = $view->getContext();

        $groups = $context->getGroups();
        if (empty($groups) && $this->exclusionStrategyGroups) {
            $context->addGroups($this->exclusionStrategyGroups);
        }

        if (null === $context->getVersion() && $this->exclusionStrategyVersion) {
            $context->setVersion($this->exclusionStrategyVersion);
        }

        if (null === $context->getSerializeNull() && null !== $this->serializeNullStrategy) {
            $context->setSerializeNull($this->serializeNullStrategy);
        }

        return $context;
    }

    /**
     * Handles a request with the proper handler.
     *
     * Decides on which handler to use based on the request format.
     *
     * @param View    $view
     * @param Request $request
     *
     * @throws UnsupportedMediaTypeHttpException
     *
     * @return Response
     */
    public function handle(View $view, Request $request = null)
    {
        if (null === $request) {
            $request = $this->requestStack->getCurrentRequest();
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
        if (($view->getStatusCode() === Response::HTTP_CREATED || $view->getStatusCode() === Response::HTTP_ACCEPTED) && $view->getData() !== null) {
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
        if ($template instanceof TemplateReferenceInterface) {
            if (null === $template->get('format')) {
                $template->set('format', $format);
            }

            if (null === $template->get('engine')) {
                $engine = $view->getEngine() ?: $this->defaultEngine;
                $template->set('engine', $engine);
            }
        }

        return $this->templating->render($template, $data);
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
            $data = [$view->getTemplateVar() => $data->getData(), 'form' => $data];
        } elseif (empty($data) || !is_array($data) || is_numeric((key($data)))) {
            $data = [$view->getTemplateVar() => $data];
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
            ? $this->urlGenerator->generate($route, (array) $view->getRouteParameters(), UrlGeneratorInterface::ABSOLUTE_URL)
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

            if ($data instanceof FormInterface && $data->isSubmitted() && !$data->isValid()) {
                $view->getContext()->setAttribute('status_code', $this->failedValidationCode);
            }

            $context = $this->getSerializationContext($view);
            $context->setAttribute('template_data', $view->getTemplateData());

            $content = $this->serializer->serialize($data, $format, $context);
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
     * Returns the data from a view.
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

        return $form;
    }
}
