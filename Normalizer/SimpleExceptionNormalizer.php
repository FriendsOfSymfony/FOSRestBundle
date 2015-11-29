<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Normalizer;

use FOS\RestBundle\Exception\NormalizedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class SimpleExceptionNormalizer implements ExceptionNormalizerInterface
{
    const DEFAULT_MESSAGE = 'error';

    private $codesMap;
    private $messagesMap;
    private $debug;

    /**
     * Constructor.
     *
     * @param array $codesMap
     * @param array $messagesMap
     * @param bool  $debug
     */
    public function __construct(array $codesMap, array $messagesMap, $debug)
    {
        $this->codesMap = $codesMap;
        $this->messagesMap = $messagesMap;
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public function normalize($exception)
    {
        $statusCode = $this->getStatusCode($exception);
        $data = [
            'code' => $statusCode,
            'status' => array_key_exists($code, Response::$statusTexts) ? Response::$statusTexts[$code] : 'error',
            'message' => $this->getExceptionMessage($exception),
        ];
        $normalizedException = new NormalizedException($statusCode, $data, $this->getExceptionHeaders($exception));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($exception)
    {
        return $this->isSubclassOf($exception, $this->codesMap) || $this->isSubclassOf($exception, $this->messagesMap);
    }

    /**
     * Extracts the exception message.
     *
     * @param object $exception
     *
     * @return string Message
     */
    private function getExceptionMessage($exception)
    {
        $showExceptionMessage = $this->isSubclassOf($exception, $this->messagesMap);

        if ($showExceptionMessage || $this->debug) {
            return $exception->getMessage();
        }

        $statusCode = $this->getStatusCode($exception);

        return array_key_exists($statusCode, Response::$statusTexts)
            ? Response::$statusTexts[$statusCode]
            : static::DEFAULT_MESSAGE;
    }

    /**
     * Determines the status code to use for the response.
     *
     * @param object $exception
     *
     * @return int
     */
    private function getExceptionStatusCode($exception)
    {
        if ($code = $this->isSubclassOf($exception, $this->codesMap)) {
            return $code;
        }
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    /**
     * Determines the headers to use for the response.
     *
     * @param object $exception
     *
     * @return array
     */
    private function getExceptionHeaders($exception)
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getHeaders();
        }

        return [];
    }

    /**
     * Extracts the exception message.
     *
     * @param object $exception
     * @param array  $exceptionMap
     *
     * @return int|bool
     */
    private function isSubclassOf($exception, $exceptionMap)
    {
        try {
            foreach ($exceptionMap as $exceptionMapClass => $value) {
                if ($value && $exception instanceof $exceptionMapClass) {
                    return $value;
                }
            }
        } catch (\ReflectionException $re) {
            return 'Invalid class in FOSRestBundle configuration: '.$re->getMessage();
        }

        return false;
    }
}
