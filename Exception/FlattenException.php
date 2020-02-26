<?php

namespace FOS\RestBundle\Exception;

use Symfony\Component\Debug\Exception\FlattenException as LegacyFlattenException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class FlattenException extends LegacyFlattenException
{
    public static function fosCreateFromThrowable(\Throwable $exception, int $statusCode = null, array $headers = []): self
    {
        $e = new static();
        $e->setMessage($exception->getMessage());
        $e->setCode($exception->getCode());

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $headers = array_merge($headers, $exception->getHeaders());
        } elseif ($exception instanceof RequestExceptionInterface) {
            $statusCode = 400;
        }

        if (null === $statusCode) {
            $statusCode = 500;
        }

        if (class_exists(Response::class) && isset(Response::$statusTexts[$statusCode])) {
            $statusText = Response::$statusTexts[$statusCode];
        } else {
            $statusText = 'Whoops, looks like something went wrong.';
        }

        $e->setStatusText($statusText);
        $e->setStatusCode($statusCode);
        $e->setHeaders($headers);
        $e->setTraceFromThrowable($exception);
        $e->setClass(\get_class($exception));
        $e->setFile($exception->getFile());
        $e->setLine($exception->getLine());

        $previous = $exception->getPrevious();

        if ($previous instanceof \Throwable) {
            $e->setPrevious(static::fosCreateFromThrowable($previous));
        }

        return $e;
    }

    public function setStatusText(string $statusText): self
    {
        $this->statusText = $statusText;

        return $this;
    }

    public function setTraceFromThrowable(\Throwable $throwable)
    {
        $this->traceAsString = $throwable->getTraceAsString();

        $this->setTrace($throwable->getTrace(), $throwable->getFile(), $throwable->getLine());
    }
}
