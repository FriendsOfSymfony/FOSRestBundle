<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional\Bundle\TestBundle\ErrorRenderer;

use JMS\Serializer\SerializerInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\ErrorRenderer\SerializerErrorRenderer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\RequestStack;

class JmsSerializerErrorRenderer implements ErrorRendererInterface
{
    private $serializer;
    private $requestStack;

    public function __construct(SerializerInterface $serializer, RequestStack $requestStack)
    {
        $this->serializer = $serializer;
        $this->requestStack = $requestStack;
    }

    public function render(\Throwable $exception): FlattenException
    {
        $flattenException = FlattenException::createFromThrowable($exception);

        return $flattenException->setAsString($this->serializer->serialize($flattenException, SerializerErrorRenderer::getPreferredFormat($this->requestStack)($flattenException)));
    }
}
