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

use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpKernel\Exception\FlattenException as HttpFlattenException;
use Symfony\Component\Debug\Exception\FlattenException as DebugFlattenException;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\Util\Codes;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Util\ExceptionWrapper;

/**
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping
 */
class ExceptionController extends ContainerAware
{
    /**
     * Creates a new ExceptionWrapper instance that can be overwritten by a custom
     * ExceptionController class.
     *
     * @param array $parameters Template parameters
     *
     * @return ExceptionWrapper ExceptionWrapper instance
     */
    protected function createExceptionWrapper(array $parameters)
    {
        /** @var ExceptionWrapperHandlerInterface $exceptionWrapperHandler */
        $exceptionWrapperHandler = $this->container->get('fos_rest.view.exception_wrapper_handler');
        return $exceptionWrapperHandler->wrap($parameters);
    }

    /**
     * Converts an Exception to a Response.
     *
     * @param Request                                       $request   Request
     * @param HttpFlattenException|DebugFlattenException    $exception A HttpFlattenException|DebugFlattenException instance
     * @param DebugLoggerInterface                          $logger    A DebugLoggerInterface instance
     * @param string                                        $format    The format to use for rendering (html, xml, ...)
     *
     * @return Response Response instance
     */
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null, $format = 'html')
    {
        /**
         * Validates that the exception that is handled by the Exception controller is either a DebugFlattenException
         * or HttpFlattenException.
         * Type hinting has been removed due to a BC change in symfony/symfony 2.3.5.
         *
         * @see https://github.com/FriendsOfSymfony/FOSRestBundle/pull/565
         */
        if (!$exception instanceof DebugFlattenException && !$exception instanceof HttpFlattenException) {
            throw new \InvalidArgumentException(sprintf(
                'ExceptionController::showAction can only accept some exceptions (%s, %s), "%s" given',
                "Symfony\Component\HttpKernel\Exception\FlattenException",
                "Symfony\Component\Debug\Exception\FlattenException",
                get_class($exception)
            ));
        }

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
                $parameters = $this->createExceptionWrapper($parameters);
            }

            $view = View::create($parameters, $code, $exception->getHeaders());
            $view->setFormat($format);

            if ($viewHandler->isFormatTemplating($format)) {
                $view->setTemplate($this->findTemplate($request, $format, $code, $this->container->get('kernel')->isDebug()));
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
     * This code comes from Symfony and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     *
     * @return string
     */
    protected function getAndCleanOutputBuffering()
    {
        $startObLevel = $this->container->get('request')->headers->get('X-Php-Ob-Level', -1);

        // ob_get_level() never returns 0 on some Windows configurations, so if
        // the level is the same two times in a row, the loop should be stopped.
        $previousObLevel = null;
        $currentContent = '';

        while (($obLevel = ob_get_level()) > $startObLevel && $obLevel !== $previousObLevel) {
            $previousObLevel = $obLevel;
            $currentContent .= ob_get_clean();
        }

        return $currentContent;
    }

    /**
     * Extract the exception message
     *
     * @param HttpFlattenException|DebugFlattenException $exception    A HttpFlattenException|DebugFlattenException instance
     * @param array                                      $exceptionMap
     *
     * @return string Message
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
     * @param HttpFlattenException|DebugFlattenException $exception A HttpFlattenException|DebugFlattenException instance
     *
     * @return string Message
     */
    protected function getExceptionMessage($exception)
    {
        $exceptionMap = $this->container->getParameter('fos_rest.exception.messages');
        $showExceptionMessage = $this->isSubclassOf($exception, $exceptionMap);

        if ($showExceptionMessage || $this->container->get('kernel')->isDebug()) {
            return $exception->getMessage();
        }

        $statusCode = $this->getStatusCode($exception);

        return array_key_exists($statusCode, Response::$statusTexts) ? Response::$statusTexts[$statusCode] : 'error';
    }

    /**
     * Determine the status code to use for the response
     *
     * @param HttpFlattenException|DebugFlattenException $exception A HttpFlattenException|DebugFlattenException instance
     *
     * @return integer An HTTP response code
     */
    protected function getStatusCode($exception)
    {
        $exceptionMap = $this->container->getParameter('fos_rest.exception.codes');
        $isExceptionMappedToStatusCode = $this->isSubclassOf($exception, $exceptionMap);

        return ($isExceptionMappedToStatusCode) ? $isExceptionMappedToStatusCode : $exception->getStatusCode();
    }

    /**
     * Determine the format to use for the response
     *
     * @param Request $request Request instance
     * @param string  $format  The format to use for rendering (html, xml, ...)
     *
     * @return string Encoding format
     */
    protected function getFormat(Request $request, $format)
    {
        $formatNegotiator = $this->container->get('fos_rest.format_negotiator');
        $format = $formatNegotiator->getBestFormat($request) ?: $format;
        $request->attributes->set('_format', $format);

        return $format;
    }

    /**
     * Determine the parameters to pass to the view layer.
     *
     * Overwrite it in a custom ExceptionController class to add additionally parameters
     * that should be passed to the view layer.
     *
     * @param ViewHandler                                       $viewHandler    The view handler instance
     * @param string                                            $currentContent The current content in the output buffer
     * @param integer                                           $code           An HTTP response code
     * @param HttpFlattenException|DebugFlattenException        $exception      A HttpFlattenException|DebugFlattenException instance
     * @param DebugLoggerInterface                              $logger         A DebugLoggerInterface instance
     * @param string                                            $format         The format to use for rendering (html, xml, ...)
     *
     * @return array Template parameters
     */
    protected function getParameters(ViewHandler $viewHandler, $currentContent, $code, $exception, DebugLoggerInterface $logger = null, $format = 'html')
    {
        $parameters  = array(
            'status' => 'error',
            'status_code' => $code,
            'status_text' => array_key_exists($code, Response::$statusTexts) ? Response::$statusTexts[$code] : "error",
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
     * This code is inspired by TwigBundle and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     *
     * @param Request $request
     * @param string  $format
     * @param integer $code       An HTTP response status code
     * @param Boolean $debug
     *
     * @return TemplateReference
     */
    protected function findTemplate(Request $request, $format, $code, $debug)
    {
        $name = $debug ? 'exception' : 'error';
        if ($debug && 'html' == $format) {
            $name = 'exception_full';
        }

        // when not in debug, try to find a template for the specific HTTP status code and format
        if (!$debug) {
            $template = new TemplateReference('TwigBundle', 'Exception', $name.$code, $format, 'twig');
            if ($this->container->get('templating')->exists($template)) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = new TemplateReference('TwigBundle', 'Exception', $name, $format, 'twig');
        if ($this->container->get('templating')->exists($template)) {
            return $template;
        }

        // default to a generic HTML exception
        $request->setRequestFormat('html');

        return new TemplateReference('TwigBundle', 'Exception', $name, 'html', 'twig');
    }
}
