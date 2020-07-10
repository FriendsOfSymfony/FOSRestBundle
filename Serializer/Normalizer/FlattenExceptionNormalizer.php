<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Serializer\Normalizer;

use FOS\RestBundle\Util\ExceptionValueMap;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @author Christian Flothmann <christian.flothmann@sensiolabs.de>
 *
 * @internal
 */
final class FlattenExceptionNormalizer implements NormalizerInterface
{
    private $statusCodeMap;
    private $messagesMap;
    private $debug;
    private $rfc7807;

    public function __construct(ExceptionValueMap $statusCodeMap, ExceptionValueMap $messagesMap, bool $debug, bool $rfc7807)
    {
        $this->statusCodeMap = $statusCodeMap;
        $this->messagesMap = $messagesMap;
        $this->debug = $debug;
        $this->rfc7807 = $rfc7807;
    }

    public function normalize($exception, $format = null, array $context = [])
    {
        if (isset($context['status_code'])) {
            $statusCode = $context['status_code'];
        } elseif (false === $statusCode = $this->statusCodeMap->resolveFromClassName($exception->getClass())) {
            $statusCode = $exception->getStatusCode();
        }

        $showMessage = $this->messagesMap->resolveFromClassName($exception->getClass());

        if ($showMessage || $this->debug) {
            $message = $exception->getMessage();
        } else {
            $message = Response::$statusTexts[$statusCode] ?? 'error';
        }

        if ($this->rfc7807) {
            if ('json' === $format) {
                $exception->setHeaders($exception->getHeaders() + ['Content-Type' => 'application/problem+json']);
            } elseif ('xml' === $format) {
                $exception->setHeaders($exception->getHeaders() + ['Content-Type' => 'application/problem+xml']);
            }

            return [
                'type' => $context['type'] ?? 'https://tools.ietf.org/html/rfc2616#section-10',
                'title' => $context['title'] ?? 'An error occurred',
                'status' => $statusCode,
                'detail' => $message,
            ];
        } else {
            return [
                'code' => $statusCode,
                'message' => $message,
            ];
        }
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof FlattenException;
    }
}
