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

@trigger_error(sprintf('The %s\ExceptionController class is deprecated since FOSRestBundle 2.8.', __NAMESPACE__), E_USER_DEPRECATED);

use FOS\RestBundle\Exception\FlattenException as FosFlattenException;
use FOS\RestBundle\Util\ExceptionValueMap;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Log\DebugLoggerInterface;

/**
 * Custom ExceptionController that uses the view layer and supports HTTP response status code mapping.
 *
 * @deprecated since 2.8
 */
class ExceptionController
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

    /**
     * @param \Throwable $exception
     */
    public function showAction(Request $request, $exception, DebugLoggerInterface $logger = null)
    {
        $currentContent = $this->getAndCleanOutputBuffering($request->headers->get('X-Php-Ob-Level', -1));

        if ($exception instanceof \Exception) {
            $code = $this->getStatusCode($exception);
        } else {
            $code = $this->getStatusCodeFromThrowable($exception);
        }
        $templateData = $this->getTemplateData($currentContent, $code, $exception, $logger);

        if ($exception instanceof \Exception) {
            $view = $this->createView($exception, $code, $templateData, $request, $this->showException);
        } else {
            $view = $this->createViewFromThrowable($exception, $code, $templateData, $request, $this->showException);
        }

        $response = $this->viewHandler->handle($view);

        return $response;
    }

    /**
     * @param int  $code
     * @param bool $showException
     *
     * @return View
     */
    protected function createView(\Exception $exception, $code, array $templateData, Request $request, $showException)
    {
        return $this->createViewFromThrowable($exception, $code, $templateData);
    }

    /**
     * @return int
     */
    protected function getStatusCode(\Exception $exception)
    {
        return $this->getStatusCodeFromThrowable($exception);
    }

    private function createViewFromThrowable(\Throwable $exception, $code, array $templateData): View
    {
        $view = new View($exception, $code, $exception instanceof HttpExceptionInterface ? $exception->getHeaders() : []);
        $view->setTemplateVar('raw_exception', false);
        $view->setTemplateData($templateData, false);

        return $view;
    }

    private function getTemplateData(string $currentContent, int $code, \Throwable $throwable, DebugLoggerInterface $logger = null): array
    {
        if (class_exists(FlattenException::class)) {
            $exception = FlattenException::createFromThrowable($throwable);
        } else {
            $exception = FosFlattenException::createFromThrowable($throwable);
        }

        return [
            'exception' => $exception,
            'status' => 'error',
            'status_code' => $code,
            'status_text' => array_key_exists($code, Response::$statusTexts) ? Response::$statusTexts[$code] : 'error',
            'currentContent' => $currentContent,
            'logger' => $logger,
        ];
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

    private function getStatusCodeFromThrowable(\Throwable $exception): int
    {
        // If matched
        if ($statusCode = $this->exceptionCodes->resolveThrowable($exception)) {
            return $statusCode;
        }

        // Otherwise, default
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return 500;
    }
}
