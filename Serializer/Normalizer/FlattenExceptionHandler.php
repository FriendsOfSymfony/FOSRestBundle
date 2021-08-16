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
use JMS\Serializer\Context;
use JMS\Serializer\GraphNavigatorInterface;
use JMS\Serializer\Handler\SubscribingHandlerInterface;
use JMS\Serializer\JsonSerializationVisitor;
use JMS\Serializer\XmlSerializationVisitor;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Christian Flothmann <christian.flothmann@sensiolabs.de>
 *
 * @internal
 */
class FlattenExceptionHandler implements SubscribingHandlerInterface
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

    public static function getSubscribingMethods(): array
    {
        return [
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'json',
                'type' => FlattenException::class,
                'method' => 'serializeToJson',
            ],
            [
                'direction' => GraphNavigatorInterface::DIRECTION_SERIALIZATION,
                'format' => 'xml',
                'type' => FlattenException::class,
                'method' => 'serializeToXml',
            ],
        ];
    }

    public function serializeToJson(JsonSerializationVisitor $visitor, FlattenException $exception, array $type, Context $context)
    {
        if ($this->rfc7807) {
            $exception->setHeaders($exception->getHeaders() + ['Content-Type' => 'application/problem+json']);
        }

        return $visitor->visitArray($this->convertToArray($exception, $context), $type, $context);
    }

    public function serializeToXml(XmlSerializationVisitor $visitor, FlattenException $exception, array $type, Context $context)
    {
        if ($this->rfc7807) {
            $exception->setHeaders($exception->getHeaders() + ['Content-Type' => 'application/problem+xml']);
        }

        $rootName = $this->rfc7807 ? 'response' : 'result';

        $data = $this->convertToArray($exception, $context);

        if (method_exists($visitor, 'setDefaultRootName')) {
            $visitor->setDefaultRootName($rootName);
        }

        $document = $visitor->getDocument(true);

        if (!$visitor->getCurrentNode()) {
            $visitor->createRoot(null, $rootName);
        }

        foreach ($data as $key => $value) {
            $entryNode = $document->createElement($key);
            $visitor->getCurrentNode()->appendChild($entryNode);
            $visitor->setCurrentNode($entryNode);

            $node = $context->getNavigator()->accept($value, null, $context);
            if (null !== $node) {
                $visitor->getCurrentNode()->appendChild($node);
            }

            $visitor->revertCurrentNode();
        }
    }

    private function convertToArray(FlattenException $exception, Context $context): array
    {
        if ($context->hasAttribute('status_code')) {
            $statusCode = $context->getAttribute('status_code');
        } elseif (null === $statusCode = $this->statusCodeMap->resolveFromClassName($exception->getClass())) {
            $statusCode = $exception->getStatusCode();
        }

        $showMessage = $this->messagesMap->resolveFromClassName($exception->getClass());

        if ($showMessage || $this->debug) {
            $message = $exception->getMessage();
        } else {
            $message = Response::$statusTexts[$statusCode] ?? 'error';
        }

        if ($this->rfc7807) {
            return [
                'type' => $context->hasAttribute('type') ? $context->getAttribute('type') : 'https://tools.ietf.org/html/rfc2616#section-10',
                'title' => $context->hasAttribute('title') ? $context->getAttribute('title') : 'An error occurred',
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
}
