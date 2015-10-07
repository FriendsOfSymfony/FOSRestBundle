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

use FOS\RestBundle\Negotiation\FormatNegotiator;
use FOS\RestBundle\Util\ExceptionWrapper;
use FOS\RestBundle\Util\StopFormatListenerException;
use FOS\RestBundle\View\ExceptionWrapperHandlerInterface;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Debug\Exception\FlattenException;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping.
 */
class ExceptionController
{
    private $exceptionWrapperHandler;
    private $formatNegotiator;
    private $viewHandler;
    private $templating;
    private $exceptionCodes;
    private $exceptionMessages;
    private $showException;

    public function __construct(
        ExceptionWrapperHandlerInterface $exceptionWrapperHandler,
        FormatNegotiator $formatNegotiator,
        ViewHandlerInterface $viewHandler,
        EngineInterface $templating,
        array $exceptionCodes,
        array $exceptionMessages,
        $showException
    ) {
        $this->exceptionWrapperHandler = $exceptionWrapperHandler;
        $this->formatNegotiator = $formatNegotiator;
        $this->viewHandler = $viewHandler;
        $this->templating = $templating;
        $this->exceptionCodes = $exceptionCodes;
        $this->exceptionMessages = $exceptionMessages;
        $this->showException = $showException;
    }

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
        return $this->exceptionWrapperHandler->wrap($parameters);
    }

    /**
     * Converts an Exception to a Response.
     *
     * @param Request              $request
     * @param FlattenException     $exception
     * @param DebugLoggerInterface $logger
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function showAction(Request $request, FlattenException $exception, DebugLoggerInterface $logger = null)
    {
        $format = $this->getFormat($request, $request->getRequestFormat());
        if (null === $format) {
            $message = 'No matching accepted Response format could be determined, while handling: ';
            $message .= $this->getExceptionMessage($exception);

            return new Response($message, Response::HTTP_NOT_ACCEPTABLE, $exception->getHeaders());
        }

        $currentContent = $this->getAndCleanOutputBuffering(
            $request->headers->get('X-Php-Ob-Level', -1)
        );
        $code = $this->getStatusCode($exception);
        $parameters = $this->getParameters($this->viewHandler, $currentContent, $code, $exception, $logger, $format);
        $showException = $request->attributes->get('showException', $this->showException);

        try {
            if (!$this->viewHandler->isFormatTemplating($format)) {
                $parameters = $this->createExceptionWrapper($parameters);
            }

            $view = View::create($parameters, $code, $exception->getHeaders());
            $view->setFormat($format);

            if ($this->viewHandler->isFormatTemplating($format)) {
                $view->setTemplate($this->findTemplate($request, $format, $code, $showException));
            }

            $response = $this->viewHandler->handle($view);
        } catch (\Exception $e) {
            $message = 'An Exception was thrown while handling: ';
            $message .= $this->getExceptionMessage($exception);
            $response = new Response($message, Response::HTTP_INTERNAL_SERVER_ERROR, $exception->getHeaders());
        }

        return $response;
    }

    /**
     * Gets and cleans any content that was already outputted.
     *
     * This code comes from Symfony and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     *
     * @return string
     */
    protected function getAndCleanOutputBuffering($startObLevel)
    {
        if (ob_get_level() <= $startObLevel) {
            return '';
        }
        Response::closeOutputBuffers($startObLevel + 1, true);

        return ob_get_clean();
    }

    /**
     * Extracts the exception message.
     *
     * @param FlattenException $exception
     * @param array            $exceptionMap
     *
     * @return int|bool
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
            return 'FOSUserBundle: Invalid class in fos_res.exception.messages: '
                    .$re->getMessage();
        }

        return false;
    }

    /**
     * Extracts the exception message.
     *
     * @param FlattenException $exception
     *
     * @return string Message
     */
    protected function getExceptionMessage($exception)
    {
        $showExceptionMessage = $this->isSubclassOf($exception, $this->exceptionMessages);

        if ($showExceptionMessage || $this->showException) {
            return $exception->getMessage();
        }

        $statusCode = $this->getStatusCode($exception);

        return array_key_exists($statusCode, Response::$statusTexts) ? Response::$statusTexts[$statusCode] : 'error';
    }

    /**
     * Determines the status code to use for the response.
     *
     * @param FlattenException $exception
     *
     * @return int
     */
    protected function getStatusCode($exception)
    {
        $isExceptionMappedToStatusCode = $this->isSubclassOf($exception, $this->exceptionCodes);

        return $isExceptionMappedToStatusCode ?: $exception->getStatusCode();
    }

    /**
     * Determines the format to use for the response.
     *
     * @param Request $request
     * @param string  $format
     *
     * @return string
     */
    protected function getFormat(Request $request, $format)
    {
        try {
            $accept = $this->formatNegotiator->getBest('', []);
            if ($accept) {
                $format = $request->getFormat($accept->getType());
            }
            $request->attributes->set('_format', $format);
        } catch (StopFormatListenerException $e) {
            $format = $request->getRequestFormat();
        }

        return $format;
    }

    /**
     * Determines the parameters to pass to the view layer.
     *
     * Overwrite it in a custom ExceptionController class to add additionally parameters
     * that should be passed to the view layer.
     *
     * @param ViewHandlerInterface $viewHandler
     * @param string               $currentContent
     * @param int                  $code
     * @param FlattenException     $exception
     * @param DebugLoggerInterface $logger
     * @param string               $format
     *
     * @return array
     */
    protected function getParameters(ViewHandlerInterface $viewHandler, $currentContent, $code, $exception, DebugLoggerInterface $logger = null, $format = 'html')
    {
        $parameters = [
            'status' => 'error',
            'status_code' => $code,
            'status_text' => array_key_exists($code, Response::$statusTexts) ? Response::$statusTexts[$code] : 'error',
            'currentContent' => $currentContent,
            'message' => $this->getExceptionMessage($exception),
            'exception' => $exception,
        ];

        if ($viewHandler->isFormatTemplating($format)) {
            $parameters['logger'] = $logger;
        }

        return $parameters;
    }

    /**
     * Finds the template for the given format and status code.
     *
     * Note this method needs to be overridden in case another
     * engine than Twig should be supported;
     *
     * This code is inspired by TwigBundle and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     *
     * @param Request $request
     * @param string  $format
     * @param int     $statusCode
     * @param bool    $showException
     *
     * @return TemplateReference
     */
    protected function findTemplate(Request $request, $format, $statusCode, $showException)
    {
        $name = $showException ? 'exception' : 'error';
        if ($showException && 'html' == $format) {
            $name = 'exception_full';
        }

        // when not in debug, try to find a template for the specific HTTP status code and format
        if (!$showException) {
            $template = new TemplateReference('TwigBundle', 'Exception', $name.$statusCode, $format, 'twig');
            if ($this->templating->exists($template)) {
                return $template;
            }
        }

        // try to find a template for the given format
        $template = new TemplateReference('TwigBundle', 'Exception', $name, $format, 'twig');
        if ($this->templating->exists($template)) {
            return $template;
        }

        // default to a generic HTML exception
        $request->setRequestFormat('html');

        return new TemplateReference('TwigBundle', 'Exception', $showException ? 'exception_full' : $name, 'html', 'twig');
    }
}
