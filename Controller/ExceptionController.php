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

use FOS\RestBundle\Util\ExceptionValueMap;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping.
 */
class ExceptionController
{
    /**
     * @var ViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @var ExceptionValueMap
     */
    private $exceptionCodes;

    /**
     * @var bool
     */
    private $showException;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        ExceptionValueMap $exceptionCodes,
        $showException
    ) {
        $this->viewHandler = $viewHandler;
        $this->exceptionCodes = $exceptionCodes;
        $this->showException = $showException;
    }

    /**
     * Converts an Exception to a Response.
     *
     * @param Request                   $request
     * @param \Exception|\Throwable     $exception
     * @param DebugLoggerInterface|null $logger
     *
     * @throws \InvalidArgumentException
     *
     * @return Response
     */
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        $currentContent = $this->getAndCleanOutputBuffering($request->headers->get('X-Php-Ob-Level', -1));

        if ($exception instanceof \Exception) {
            $code = $this->getStatusCode($exception);
        } else {
            $code = $this->getStatusCodeFromThrowable($exception);
        }
        if ($exception instanceof \Exception) {
            $view = $this->createView($exception, $code, $request, $this->showException);
        } else {
            $view = new View($exception, $code, $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : []);
        }

        $response = $this->viewHandler->handle($view);

        return $response;
    }

    /**
     * @param \Exception $exception
     * @param int        $code
     * @param Request    $request
     * @param bool       $showException
     *
     * @return View
     */
    protected function createView(\Exception $exception, $code, Request $request, $showException)
    {
        return new View($exception, $code, $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : []);
    }

    /**
     * Determines the status code to use for the response.
     *
     * @param \Exception $exception
     *
     * @return int
     */
    protected function getStatusCode(\Exception $exception)
    {
        return $this->getStatusCodeFromThrowable($exception);
    }

    /**
     * Gets and cleans any content that was already outputted.
     *
     * This code comes from Symfony and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     *
     * @return string
     */
    private function getAndCleanOutputBuffering($startObLevel)
    {
        if (ob_get_level() <= $startObLevel) {
            return '';
        }
        Response::closeOutputBuffers($startObLevel + 1, true);

        return ob_get_clean();
    }

    /**
     * Determines the status code to use for the response.
     *
     * @param \Throwable $exception
     *
     * @return int
     */
    private function getStatusCodeFromThrowable(\Throwable $exception)
    {
        // If matched
        if ($statusCode = $this->exceptionCodes->resolveException($exception)) {
            return $statusCode;
        }

        // Otherwise, default
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }
}
