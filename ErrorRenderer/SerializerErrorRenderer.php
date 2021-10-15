<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\ErrorRenderer;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use JMS\Serializer\Exception\UnsupportedFormatException;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

/**
 * @internal
 */
final class SerializerErrorRenderer implements ErrorRendererInterface
{
    private $serializer;
    private $format;
    private $fallbackErrorRenderer;
    private $debug;

    /**
     * @param string|callable(FlattenException) $format
     * @param string|bool                       $debug
     */
    public function __construct(Serializer $serializer, $format, ErrorRendererInterface $fallbackErrorRenderer = null, $debug = false)
    {
        if (!is_string($format) && !is_callable($format)) {
            throw new \TypeError(sprintf('Argument 2 passed to "%s()" must be a string or a callable, "%s" given.', __METHOD__, \is_object($format) ? \get_class($format) : \gettype($format)));
        }

        if (!is_bool($debug) && !is_callable($debug)) {
            throw new \TypeError(sprintf('Argument 4 passed to "%s()" must be a boolean or a callable, "%s" given.', __METHOD__, \is_object($debug) ? \get_class($debug) : \gettype($debug)));
        }

        $this->serializer = $serializer;
        $this->format = $format;
        $this->fallbackErrorRenderer = $fallbackErrorRenderer;
        $this->debug = $debug;
    }

    public function render(\Throwable $exception): FlattenException
    {
        $flattenException = FlattenException::createFromThrowable($exception);

        try {
            $format = is_callable($this->format) ? ($this->format)($flattenException) : $this->format;

            $context = new Context();
            $context->setAttribute('exception', $exception);
            $context->setAttribute('debug', is_callable($this->debug) ? ($this->debug)($exception) : $this->debug);

            $headers = [
                'Content-Type' => Request::getMimeTypes($format)[0] ?? $format,
                'Vary' => 'Accept',
            ];

            return $flattenException->setAsString($this->serializer->serialize($flattenException, $format, $context))->setHeaders($flattenException->getHeaders() + $headers);
        } catch (NotEncodableValueException|UnsupportedFormatException $e) {
            return $this->fallbackErrorRenderer->render($exception);
        }
    }

    /**
     * @see \Symfony\Component\ErrorHandler\ErrorRenderer\SerializerErrorRenderer::getPreferredFormat
     */
    public static function getPreferredFormat(RequestStack $requestStack): \Closure
    {
        return static function () use ($requestStack) {
            if (!$request = $requestStack->getCurrentRequest()) {
                throw class_exists(NotEncodableValueException::class) ? new NotEncodableValueException() : new UnsupportedFormatException();
            }

            return $request->getPreferredFormat();
        };
    }

    /**
     * @see \Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer::isDebug
     */
    public static function isDebug(RequestStack $requestStack, bool $debug): \Closure
    {
        return static function () use ($requestStack, $debug): bool {
            if (!$request = $requestStack->getCurrentRequest()) {
                return $debug;
            }

            return $debug && $request->attributes->getBoolean('showException', true);
        };
    }
}
