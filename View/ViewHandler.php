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

use Symfony\Component\HttpFoundation\RedirectResponse;

use Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\DependencyInjection\ContainerAware,
    Symfony\Component\Serializer\SerializerInterface,
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
class ViewHandler extends ContainerAware
{
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
    protected $failedValidationCode;

    /**
     * @var array if to force a redirect for the given key format, with value being the status code to use
     */
    protected $forceRedirects;

    /**
     * Constructor
     *
     * @param array $formats            The supported formats
     * @param int $failedValidationCode The HTTP response status code for a failed validation
     * @param string $defaultFormKey    The default parameter form key
     * @param array $forceRedirects     If to force a redirect for the given key format, with value being the status code to use
     */
    public function __construct(array $formats = null, $failedValidationCode = Codes::HTTP_BAD_REQUEST, array $forceRedirects = null)
    {
        $this->formats = (array)$formats;
        $this->failedValidationCode = $failedValidationCode;
        $this->forceRedirects = (array)$forceRedirects;
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
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException('Registered view callback must be callable.');
        }

        $this->customHandlers[$format] = $callback;
    }

    /**
     * Gets a response HTTP status code
     *
     * By default it will return 200, however for the first form instance in the top level of the parameters it will
     * - set the status code to the failed_validation configuration is the form instance has errors
     * - set inValidFormKey so that the form instance can be replaced with createView() if the format uses templating
     *
     * @return int HTTP status code
     */
    private function getStatusCodeFromView(View $view, $parameters)
    {
        if (null !== $code = $view->getStatusCode()) {
            return $code;
        }

        if (empty($parameters['form'])) {
            return Codes::HTTP_OK;
        }

        return $parameters['form']->isBound() && !$parameters['form']->isValid()
            ? $this->failedValidationCode : Codes::HTTP_OK;
    }

    /**
     * If the given format uses the templating system for rendering
     *
     * @param $format
     * @return bool
     */
    public function isFormatTemplating($format)
    {
        return !empty($this->formats[$format]) && 'templating' === $this->formats[$format];
    }

    /**
     * Handles a request with the proper handler
     *
     * Decides on which handler to use based on the request format
     *
     * @param Request $request Request object
     * @param View $view
     *
     * @return Response
     */
    public function handle(Request $request, View $view)
    {
        $format = $view->getFormat() ?: $request->getRequestFormat();

        if (isset($this->customHandlers[$format])) {
            return call_user_func($this->customHandlers[$format], $request, $view);
        } elseif ($this->supports($format)) {
            return $this->createResponse($request, $view, $format);
        }

        return new Response("Format '$format' not supported, handler must be implemented", Codes::HTTP_UNSUPPORTED_MEDIA_TYPE);
    }

    /**
     * Generic transformer
     *
     * Handles target and parameter transformation into a response
     *
     * @param Request $request
     * @param View $view
     * @param Response $response
     * @param string $format
     *
     * @return Response
     */
    protected function createResponse(Request $request, View $view, $format)
    {
        $headers = $view->getHeaders();
        $headers['Content-Type'] = $request->getMimeType($format);

        $parameters = $view->getParameters();

        // handle redirects
        if (isset($headers['Location'])) {
            $url = parse_url($headers['Location']);
            if (!$url || empty($url['host'])) {
                $headers['Location'] = $this->container->get('router')->generate($headers['Location'], $parameters, true);
            }

            $code = isset($this->forceRedirects[$format]) ? $this->forceRedirects[$format] : $this->getStatusCodeFromView($view, $parameters);

            if ('html' === $format) {
                $response = new RedirectResponse($headers['Location'], $code);
                $response->headers->replace($headers);

                return $response;
            }

            return new Response('', $code, $headers);
        }

        if ($this->isFormatTemplating($format)) {
            if (!is_array($parameters)) {
                throw new \RuntimeException(sprintf(
                    'Parameters must be an array if you allow a templating-aware format (%s).',
                    $format
                ));
            }

            if (isset($parameters['form']) && $parameters['form'] instanceof FormInterface) {
                $parameters['form'] = $parameters['form']->createView();
            }

            $template = $view->getTemplate();
            if ($template instanceOf TemplateReference) {
                if (null === $template->get('format')) {
                    $template->set('format', $format);
                }

                if (null === $template->get('engine')) {
                    $template->set('engine', $view->getEngine());
                }
            }

            $content = $this->container->get('templating')->render($template, $parameters);
        } else {
            $content = $this->container->get('serializer')->serialize($parameters, $format);
        }

        return new Response($content, $this->getStatusCodeFromView($view, $parameters), $headers);
    }
}
