<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference,
    Symfony\Component\DependencyInjection\ContainerAware,
    Symfony\Component\HttpKernel\Exception\FlattenException,
    Symfony\Component\HttpKernel\Log\DebugLoggerInterface,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

use FOS\Rest\Util\Codes,
    FOS\RestBundle\View\ViewHandler,
    FOS\RestBundle\View\View,
    FOS\RestBundle\Util\ExceptionWrapper;

/**
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping
 */
class ExceptionController extends ContainerAware
{
    /**
     * Converts an Exception to a Response.
     *
     * @param FlattenException     $exception   A FlattenException instance
     * @param DebugLoggerInterface $logger      A DebugLoggerInterface instance
     * @param string               $format      The format to use for rendering (html, xml, ...)
     * @param integer              $code        An HTTP response code
     * @param string               $message     An HTTP response status message
     * @param array                $headers     HTTP response headers
     *
     * @return Response                         Response instance
     */
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null, $format = 'html')
    {
        $format = $this->getFormat($request, $format);
        if (null === $format) {
            $message = 'No matching accepted Response format could be determined, while handling: ';
            $message.= $this->getExceptionMessage($exception);
            return new Response($message, Codes::HTTP_NOT_ACCEPTABLE, $exception->getHeaders());
        }

        $currentContent = $this->getAndCleanOutputBuffering();
        $code = $this->getStatusCode($exception);
        $viewHandler = $this->container->get('fos_rest.view_handler');
        $parameters = $this->getParameters($viewHandler, $currentContent, $code, $exception, $logger, $format);

        try {
            if (!$viewHandler->isFormatTemplating($format)) {
                $parameters = new ExceptionWrapper($parameters);
            }

            $view = View::create($parameters, $code, $exception->getHeaders());
            $view->setFormat($format);

            if ($viewHandler->isFormatTemplating($format)) {
                $view->setTemplate($this->findTemplate($format, $code));
            }

            $response = $viewHandler->handle($view);
        } catch (\Exception $e) {
            $message = 'An Exception was thrown while handling: ';
            $message.= $this->getExceptionMessage($exception);
            $response = new Response($message, Codes::HTTP_INTERNAL_SERVER_ERROR, $exception->getHeaders());
        }

        return $response;
    }

    /**
     * Get and clean any content that was already outputted
     *
     * @return string
     */
    protected function getAndCleanOutputBuffering()
    {
        // the count variable avoids an infinite loop on
        // some Windows configurations where ob_get_level()
        // never reaches 0
        $count = 100;
        $startObLevel = $this->container->get('request')->headers->get('X-Php-Ob-Level', -1);;
        $currentContent = '';
        while (ob_get_level() > $startObLevel && --$count) {
            $currentContent .= ob_get_clean();
        }

        return $currentContent;
    }

    /**
     * Extract the exception message
     *
     * @param FlattenException     $exception   A FlattenException instance
     * @param array                $exceptionMap
     *
     * @return string                           Message
     */
    protected function isSubclassOf($exception, $exceptionMap)
    {
        $exceptionClass = $exception->getClass();
        $reflectionExceptionClass = new \ReflectionClass($exceptionClass);
        try {
            foreach ($exceptionMap as $exceptionMapClass => $value) {
                if ($value
                    && ($exceptionClass === $exceptionMapClass || $reflectionExceptionClass->isSubclassOf($exceptionMapClass))
                ) {
                    return $value;
                }
            }
        } catch (\ReflectionException $re) {
            return "FOSUserBundle: Invalid class in  fos_res.exception.messages: "
                    . $re->getMessage();
        }

        return false;
    }

    /**
     * Extract the exception message
     *
     * @param FlattenException     $exception   A FlattenException instance
     *
     * @return string                           Message
     */
    protected function getExceptionMessage($exception)
    {
        $exceptionMap = $this->container->getParameter('fos_rest.exception.messages');
        $showExceptionMessage = $this->isSubclassOf($exception, $exceptionMap);

        return $showExceptionMessage || $this->container->get('kernel')->isDebug() ? $exception->getMessage() : '';
    }

    /**
     * Determine the status code to use for the response
     *
     * @param FlattenException     $exception   A FlattenException instance
     *
     * @return integer                          An HTTP response code
     */
    protected function getStatusCode($exception)
    {
        $exceptionMap = $this->container->getParameter('fos_rest.exception.codes');
        $isExceptionMappedToStatusCode = $this->isSubclassOf($exception, $exceptionMap);;

        return ($isExceptionMappedToStatusCode) ? $isExceptionMappedToStatusCode : $exception->getStatusCode();
    }

    /**
     * Determine the format to use for the response
     *
     * @param Request              $request   Request instance
     * @param string               $format    The format to use for rendering (html, xml, ...)
     *
     * @return string                         Encoding format
     */
    protected function getFormat(Request $request, $format)
    {
        $request->attributes->set('_format', $format);
        $priorities = $this->container->getParameter('fos_rest.default_priorities');
        $preferExtension = $this->container->getParameter('fos_rest.prefer_extension');
        $formatNegotiator = $this->container->get('fos_rest.format_negotiator');

        return $formatNegotiator->getBestFormat($request, $priorities, $preferExtension) ?: $format;
    }

    /**
     * Determine the parameters to pass to the view layer
     *
     * @param ViewHandler          $viewHandler     The view handler instance
     * @param string               $currentContent  The current content in the output buffer
     * @param integer              $code            An HTTP response code
     * @param FlattenException     $exception       A FlattenException instance
     * @param DebugLoggerInterface $logger          A DebugLoggerInterface instance
     * @param string               $format          The format to use for rendering (html, xml, ...)
     *
     * @return array                                Template parameters
     */
    protected function getParameters(ViewHandler $viewHandler, $currentContent, $code, FlattenException $exception, DebugLoggerInterface $logger = null, $format = 'html')
    {
        $parameters  = array(
            'status' => 'error',
            'status_code' => $code,
            'status_text' => Response::$statusTexts[$code],
            'currentContent' => $currentContent,
            'message' => $this->getExceptionMessage($exception),
        );

        if ($viewHandler->isFormatTemplating($format)) {
            $parameters['exception'] = $exception;
            $parameters['logger'] = $logger;
        }

        return $parameters;
    }

    /**
     * Find the template for the given format and status code
     *
     * Note this method needs to be overridden in case another
     * engine than Twig should be supported;
     *
     * @param string               $format          The format to use for rendering (html, xml, ...)
     * @param integer              $code            An HTTP response code
     *
     * @return TemplateReference
     */
    protected function findTemplate($format, $code)
    {
        $templating = $this->container->get('templating');
        $debug = $this->container->get('kernel')->isDebug();

        $name = $debug ? 'exception' : 'error';
        if ($debug && 'html' == $format) {
            $name = 'exception_full';
        }

        // when not in debug, try to find a template for the specific HTTP status code and format
        if (!$debug) {
            $template = new TemplateReference('TwigBundle', 'Exception', $name.$code, $format, 'twig');
            if ($templating->exists($template)) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = new TemplateReference('TwigBundle', 'Exception', $name, $format, 'twig');
        if ($templating->exists($template)) {
            return $template;
        }

        return new TemplateReference('TwigBundle', 'Exception', $name, 'html', 'twig');
    }
}
