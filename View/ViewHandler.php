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

use Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpKernel\Exception\HttpException,
    Symfony\Component\DependencyInjection\ContainerAware,
    Symfony\Component\Form\FormInterface,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;

use FOS\RestBundle\Response\Codes;

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
     * @param array $formats the supported formats as keys and if the given formats uses templating is denoted by a true value
     * @param int $failedValidationCode The HTTP response status code for a failed validation
     * @param string $defaultFormKey    The default parameter form key
     * @param array $forceRedirects     If to force a redirect for the given key format, with value being the status code to use
     * @param string $defaultEngine default engine (twig, php ..)
     */
    public function __construct(array $formats = null, $failedValidationCode = Codes::HTTP_BAD_REQUEST, array $forceRedirects = null, $defaultEngine = 'twig')
    {
        $this->formats = (array)$formats;
        $this->failedValidationCode = $failedValidationCode;
        $this->forceRedirects = (array)$forceRedirects;
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
     * The handler must have the following signature: handler($viewObject, $request, $response)
     * It can use the public methods of this class to retrieve the needed data and return a
     * Response object ready to be sent.
     *
     * @param string $format the format that is handled
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
     * @return int HTTP status code
     */
    private function getStatusCode(View $view)
    {
        if (null !== $code = $view->getStatusCode()) {
            return $code;
        }

        $data = $view->getData();
        if (!is_array($data) || empty($data['form']) || !($data['form'] instanceof FormInterface)) {
            return Codes::HTTP_OK;
        }

        return $data['form']->isBound() && !$data['form']->isValid()
            ? $this->failedValidationCode : Codes::HTTP_OK;
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
     * @return Symfony\Component\Serializer\SerializerInterface
     */
    protected function getSerializer()
    {
        return $this->container->get('fos_rest.serializer');
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
     * @param View $view
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
     * @param View $view
     * @param string $location
     * @param string $format
     *
     * @return Response
     */
    public function createRedirectResponse(View $view, $location, $format)
    {
        $view->setHeader('Location', $location);

        $code = isset($this->forceRedirects[$format])
            ? $this->forceRedirects[$format] : $this->getStatusCode($view);

        if ('html' === $format && isset($this->forceRedirects[$format])) {
            $response = new RedirectResponse($location, $code);
            $response->headers->replace($view->getHeaders());
        } else {
            $response = new Response('', $code, $view->getHeaders());
        }

        return $response;
    }

    /**
     * Render the view data with the given template
     *
     * @param View $view
     * @param string $format
     *
     * @return string
     */
    public function renderTemplate(View $view, $format)
    {
        $data = $view->getData();
        if (null === $data) {
            $data = array();
        }

        if (!is_array($data)) {
            throw new \RuntimeException(sprintf(
                'data must be an array if you allow a templating-aware format (%s).',
                $format
            ));
        }

        if (isset($data['form']) && $data['form'] instanceof FormInterface) {
            $data['form'] = $data['form']->createView();
        }

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
     * Handles creation of a Response using either redirection or the templating/serializer service
     *
     * @param View $view
     * @param Request $request
     * @param string $format
     *
     * @return Response
     */
    public function createResponse(View $view, Request $request, $format)
    {
        $view->setHeader('Content-Type', $request->getMimeType($format));

        $route = $view->getRoute();
        $location = $route
            ? $this->getRouter()->generate($route, (array)$view->getData(), true)
            : $view->getLocation();

        if ($location) {
            return $this->createRedirectResponse($view, $location, $format);
        }

        $content = $this->isFormatTemplating($format)
            ? $this->renderTemplate($view, $format)
            : $this->getSerializer()->serialize($view->getData(), $format);

        return new Response($content, $this->getStatusCode($view), $view->getHeaders());
    }
}
