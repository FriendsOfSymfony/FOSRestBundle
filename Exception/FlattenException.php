<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Exception;

use Symfony\Component\Debug\Exception\FlattenException as LegacyFlattenException;
use Symfony\Component\HttpFoundation\Exception\RequestExceptionInterface;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

if (method_exists(LegacyFlattenException::class, 'createFromThrowable')) {
    /**
     * @internal
     */
    class FlattenException extends LegacyFlattenException
    {
    }
} else {
    /**
     * @internal
     */
    class FlattenException extends LegacyFlattenException
    {
        private $traceAsString;

        /**
         * @return static
         */
        public static function createFromThrowable(\Throwable $exception, int $statusCode = null, array $headers = [])
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

            $e->setStatusCode($statusCode);
            $e->setHeaders($headers);
            $e->setTraceFromThrowable($exception);
            $e->setClass($exception instanceof FatalThrowableError ? $exception->getOriginalClassName() : \get_class($exception));
            $e->setFile($exception->getFile());
            $e->setLine($exception->getLine());

            $previous = $exception->getPrevious();

            if ($previous instanceof \Throwable) {
                $e->setPrevious(static::createFromThrowable($previous));
            }

            return $e;
        }

        public function setTraceFromThrowable(\Throwable $throwable)
        {
            $this->traceAsString = $throwable->getTraceAsString();

            return $this->setTrace($throwable->getTrace(), $throwable->getFile(), $throwable->getLine());
        }
    }
}
