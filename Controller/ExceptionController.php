<?php

namespace FOS\RestBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\ExceptionController as BaseExceptionController,
    Symfony\Component\HttpKernel\Exception\FlattenException,
    Symfony\Component\HttpKernel\Log\DebugLoggerInterface,
    Symfony\Component\HttpFoundation\Response;

/*
 * This file is part of the FOS/RestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

/**
 * ExceptionController.
 *
 * Set in the app config.yml:
 *
 * framework:
 *   exception_controller: 'FOS\RestBundle\Controller\ExceptionController::showAction'
 * 
 */
class ExceptionController extends BaseExceptionController
{
    /**
     * Converts an Exception to a Response.
     *
     * @param FlattenException     $exception A FlattenException instance
     * @param DebugLoggerInterface $logger    A DebugLoggerInterface instance
     * @param string               $format    The format to use for rendering (html, xml, ...)
     * @param integer              $code      An HTTP response code
     * @param string               $message   An HTTP response status message
     * @param array                $headers   HTTP response headers
     */
    public function showAction(FlattenException $exception, DebugLoggerInterface $logger = null, $format = 'html', $code = 500, $message = null, array $headers = array())
    {
        $currentContent = '';
        // @codeCoverageIgnoreStart
        while (ob_get_level()) {
            $currentContent .= ob_get_clean();
        }
        // @codeCoverageIgnoreEnd

        $format = $this->getFormat($format);
        $parameters = $this->getParameters($currentContent, $exception, $logger, $format, $code, $message);
        $code = $this->getStatusCode($exception, $code);

        try {
            $view = $this->container->get('fos_rest');

            $view->setFormat($format);
            $view->setTemplate($this->getTemplate($format));
            $view->setParameters($parameters);
            $view->setStatusCode($code);

            $response = $view->handle();
        } catch (\Exception $e) {
            $response = new Response(var_export($parameters, true), $code);
        }

        $response->headers->replace($headers);

        return $response;
    }

    /**
     * Extract the exception message
     *
     * @param FlattenException     $exception A FlattenException instance
     */
    protected function getExceptionMessage($exception)
    {
        return '';
    }

    /**
     * Determine the status code to use for the response
     *
     * @param FlattenException     $exception A FlattenException instance
     * @param integer              $code      An HTTP response code
     *
     * return integer              $code      An HTTP response code
     */
    protected function getStatusCode($exception, $code)
    {
        return $code;
    }

    /**
     * Determine the format to use for the response
     *
     * @param string               $format    The format to use for rendering (html, xml, ...)
     */
    protected function getFormat($format)
    {
        return $format;
    }

    /**
     * Determine the template to use for the response
     *
     * @param string               $format    The format to use for rendering (html, xml, ...)
     */
    protected function getTemplate($format)
    {
        $name = $this->container->get('kernel')->isDebug() ? 'exception' : 'error';
        if ($this->container->get('kernel')->isDebug() && 'html' == $format) {
            $name = 'exception_full';
        }

        return array(
            'bundle' => 'FrameworkBundle',
            'controller' => 'Exception',
            'name' => $name,
            'format' => $format,
        );
    }

    /**
     * Determine the parameters to pass to the view layer
     *
     * @param string               $currentContent The current content in the output buffer
     * @param FlattenException     $exception A FlattenException instance
     * @param DebugLoggerInterface $logger    A DebugLoggerInterface instance
     * @param string               $format    The format to use for rendering (html, xml, ...)
     * @param integer              $code      An HTTP response code
     * @param string               $message   An HTTP response status message
     */
    protected function getParameters($currentContent, FlattenException $exception, DebugLoggerInterface $logger = null, $format = 'html', $code = 500, $message = null)
    {
        return array(
            'status' => 'error',
            'message' => $this->getExceptionMessage($exception),
            'status_code' => $code,
            'status_text' => $message ?: Response::$statusTexts[$code],
            'currentContent' => $currentContent,
        );
    }
}
