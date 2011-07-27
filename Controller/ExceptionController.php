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
    Symfony\Component\HttpFoundation\Response,
    Symfony\Bundle\TwigBundle\Controller\ExceptionController as BaseExceptionController;

use FOS\RestBundle\Response\Codes,
    FOS\RestBundle\Serializer\Encoder\TemplatingAwareEncoderInterface;

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
    public function showAction(FlattenException $exception, DebugLoggerInterface $logger = null, $format = 'html')
    {
        // the count variable avoids an infinite loop on
        // some Windows configurations where ob_get_level()
        // never reaches 0
        $count = 100;
        $currentContent = '';
        while (ob_get_level() && --$count) {
            $currentContent .= ob_get_clean();
        }

        $format = $this->getFormat($format);
        if (null === $format) {
            $message = 'No matching accepted Response format could be determined';
            return new Response($message, Codes::HTTP_NOT_ACCEPTABLE);
        }

        $code = $this->getStatusCode($exception);
        $parameters = $this->getParameters($currentContent, $code, $exception, $logger, $format);

        try {
            $view = $this->container->get('fos_rest.view');

            $view->setFormat($format);
            $serializer = $view->getSerializer();
            $encoder = $serializer->getEncoder($format);

            if ($encoder instanceof TemplatingAwareEncoderInterface) {
                $templating = $this->container->get('templating');
                $template = $this->findTemplate($templating, $format, $code, $this->container->get('kernel')->isDebug());
                $template->set('engine', null);
                $view->setTemplate($template);
            }

            $view->setParameters($parameters);
            $view->setStatusCode($code);

            $response = $view->handle();
        } catch (\Exception $e) {
            $message = $this->container->get('kernel')->isDebug() ? $e->getMessage() : 'Internal Server Error';
            $response = new Response($message, Codes::HTTP_INTERNAL_SERVER_ERROR);
        }

        $response->headers->replace($exception->getHeaders());

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

        return empty($exceptionMap[$exceptionClass]) ? '' : $exception->getMessage();
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
        $exceptionClass = $exception->getClass();
        $exceptionMap = $this->container->getParameter('fos_rest.exception.codes');

        return isset($exceptionMap[$exceptionClass]) ? $exceptionMap[$exceptionClass] : $exception->getStatusCode();
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
     * Determine the parameters to pass to the view layer
     *
     * @param string               $currentContent  The current content in the output buffer
     * @param integer              $code            An HTTP response code
     * @param FlattenException     $exception       A FlattenException instance
     * @param DebugLoggerInterface $logger          A DebugLoggerInterface instance
     * @param string               $format          The format to use for rendering (html, xml, ...)
     *
     * @return array                                Template parameters
     */
    protected function getParameters($currentContent, $code, FlattenException $exception, DebugLoggerInterface $logger = null, $format = 'html')
    {
        $parameters  = array(
            'status' => 'error',
            'status_code' => $code,
            'status_text' => Response::$statusTexts[$code],
            'currentContent' => $currentContent,
            'message' => $this->getExceptionMessage($exception),
        );

        if ($format === 'html') {
            $parameters['exception'] = $exception;
            $parameters['logger'] = $logger;
        }

        return $parameters;
    }
}
