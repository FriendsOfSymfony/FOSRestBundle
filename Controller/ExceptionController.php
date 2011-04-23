<?php

namespace FOS\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ExceptionController as BaseExceptionController,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference,
    Symfony\Component\HttpKernel\Exception\FlattenException,
    Symfony\Component\HttpKernel\Log\DebugLoggerInterface,
    Symfony\Component\HttpFoundation\Response;

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
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping
 */
class ExceptionController extends BaseExceptionController
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
    public function showAction(FlattenException $exception, DebugLoggerInterface $logger = null, $format = 'html', $code = null, $message = null, array $headers = array())
    {
        $currentContent = '';
        while (ob_get_level()) {
            $currentContent .= ob_get_clean();
        }

        $format = $this->getFormat($format);
        $code = $this->getStatusCode($exception, $code);
        $parameters = $this->getParameters($currentContent, $exception, $logger, $format, $code, $message);

        try {
            $view = $this->container->get('fos_rest.view');

            $view->setFormat($format);
            $view->setTemplate($this->getTemplate($format));
            $view->setParameters($parameters);
            $view->setStatusCode($code);

            $response = $view->handle();
        } catch (\Exception $e) {
            $response = new Response('Internal Server Error', Codes::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->headers->replace($headers);

        return $response;
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
        $exceptionClass = $exception->getClass();
        $exceptionMap = $this->container->getParameter('fos_rest.exception.messages');

        if (empty($exceptionMap[$exceptionClass])) {
            $exceptionClass = '*';
        }

        return $exceptionMap[$exceptionClass] ? $exception->getMessage() : '';
    }

    /**
     * Determine the status code to use for the response
     *
     * @param FlattenException     $exception   A FlattenException instance
     * @param integer              $code        An HTTP response code
     *
     * @return integer                          An HTTP response code
     */
    protected function getStatusCode($exception, $code)
    {
        if (isset($code)) {
            return $code;
        }

        $exceptionClass = $exception->getClass();
        $exceptionMap = $this->container->getParameter('fos_rest.exception.codes');
        return isset($exceptionMap[$exceptionClass]) ? $exceptionMap[$exceptionClass] : $exceptionMap['*'];
    }

    /**
     * Determine the format to use for the response
     *
     * @param string               $format    The format to use for rendering (html, xml, ...)
     *
     * @return string                         Encoding format
     */
    protected function getFormat($format)
    {
        return $format;
    }

    /**
     * Determine the template to use for the response
     *
     * @param string               $format    The format to use for rendering (html, xml, ...)
     *
     * @return TemplateReference              Template reference
     */
    protected function getTemplate($format)
    {
        if ($this->container->get('kernel')->isDebug()) {
            $name = 'html' === $format ? 'exception_full' : 'exception';
        } else {
            $name = 'error';
        }

        return new TemplateReference('FrameworkBundle', 'Exception', $name, $format);
    }

    /**
     * Determine the parameters to pass to the view layer
     *
     * @param string               $currentContent  The current content in the output buffer
     * @param FlattenException     $exception       A FlattenException instance
     * @param DebugLoggerInterface $logger          A DebugLoggerInterface instance
     * @param string               $format          The format to use for rendering (html, xml, ...)
     * @param integer              $code            An HTTP response code
     * @param string               $message         An HTTP response status message
     *
     * @return array                                Template parameters
     */
    protected function getParameters($currentContent, FlattenException $exception, DebugLoggerInterface $logger = null, $format = 'html', $code = 500, $message = '')
    {
        $parameters  = array(
            'status' => 'error',
            'message' => $this->getExceptionMessage($exception),
            'status_code' => $code,
            'status_text' => $message && isset(Response::$statusTexts[$code]) ? $message : Response::$statusTexts[$code],
            'currentContent' => $currentContent,
        );

        if ($format === 'html') {
            $parameters['exception'] = $exception;
            $parameters['logger'] = $logger;
        }

        return $parameters;
    }
}
