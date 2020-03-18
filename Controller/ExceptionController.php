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

/**
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping.
 */
final class ExceptionController
{
    private $viewHandler;
    private $exceptionCodes;
    private $showException;

    public function __construct(
        ViewHandlerInterface $viewHandler,
        ExceptionValueMap $exceptionCodes,
        bool $showException
    ) {
        $this->viewHandler = $viewHandler;
        $this->exceptionCodes = $exceptionCodes;
        $this->showException = $showException;
    }

    public function showAction(Request $request, \Throwable $exception): Response
    {
        $this->getAndCleanOutputBuffering($request->headers->get('X-Php-Ob-Level', -1));

        $view = new View($exception, $this->getStatusCode($exception), $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : []);

        $response = $this->viewHandler->handle($view);

        return $response;
    }

    /**
     * Gets and cleans any content that was already outputted.
     *
     * This code comes from Symfony and should be synchronized on a regular basis
     * see src/Symfony/Bundle/TwigBundle/Controller/ExceptionController.php
     */
    private function getAndCleanOutputBuffering($startObLevel): string
    {
        if (ob_get_level() <= $startObLevel) {
            return '';
        }
        Response::closeOutputBuffers($startObLevel + 1, true);

        return ob_get_clean();
    }

    private function getStatusCode(\Throwable $exception): int
    {
        // If matched
        if (null !== $statusCode = $this->exceptionCodes->resolveException($exception)) {
            return $statusCode;
        }

        // Otherwise, default
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }
}
