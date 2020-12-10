<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\ErrorRenderer;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\ErrorRenderer\SerializerErrorRenderer;
use FOS\RestBundle\Serializer\Serializer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class SerializerErrorRendererTest extends TestCase
{
    protected function setUp(): void
    {
        if (!interface_exists(ErrorRendererInterface::class)) {
            $this->markTestSkipped();
        }
    }

    public function testSerializeFlattenExceptionWithStringFormat()
    {
        $serializer = $this->createMock(Serializer::class);
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($this->isInstanceOf(FlattenException::class), 'json', $this->isInstanceOf(Context::class))
            ->willReturn('serialized FlattenException');

        $errorRenderer = new SerializerErrorRenderer($serializer, 'json');
        $flattenException = $errorRenderer->render(new NotFoundHttpException());

        $this->assertSame('serialized FlattenException', $flattenException->getAsString());
    }

    public function testSerializeFlattenExceptionWithCallableFormat()
    {
        $serializer = $this->createMock(Serializer::class);
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($this->isInstanceOf(FlattenException::class), 'json', $this->isInstanceOf(Context::class))
            ->willReturn('serialized FlattenException');

        $format = function (FlattenException $flattenException) {
            return 'json';
        };

        $errorRenderer = new SerializerErrorRenderer($serializer, $format);
        $flattenException = $errorRenderer->render(new NotFoundHttpException());

        $this->assertSame('serialized FlattenException', $flattenException->getAsString());
    }

    public function testSerializeFlattenExceptionUsingGetPreferredFormatMethod()
    {
        $serializer = $this->createMock(Serializer::class);
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($this->isInstanceOf(FlattenException::class), 'json', $this->isInstanceOf(Context::class))
            ->willReturn('serialized FlattenException');

        $request = new Request();
        $request->attributes->set('_format', 'json');

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $format = SerializerErrorRenderer::getPreferredFormat($requestStack);

        $errorRenderer = new SerializerErrorRenderer($serializer, $format);
        $flattenException = $errorRenderer->render(new NotFoundHttpException());

        $this->assertSame('serialized FlattenException', $flattenException->getAsString());
    }

    public function testFallbackErrorRendererIsUsedWhenFormatCannotBeDetected()
    {
        $exception = new NotFoundHttpException();
        $flattenException = new FlattenException();

        $fallbackErrorRenderer = $this->createMock(ErrorRendererInterface::class);
        $fallbackErrorRenderer
            ->expects($this->once())
            ->method('render')
            ->with($exception)
            ->willReturn($flattenException);

        $serializer = $this->createMock(Serializer::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with($this->isInstanceOf(FlattenException::class), 'json', $this->isInstanceOf(Context::class))
            ->willThrowException(new NotEncodableValueException());

        $errorRenderer = new SerializerErrorRenderer($serializer, 'json', $fallbackErrorRenderer);

        $this->assertSame($flattenException, $errorRenderer->render($exception));
    }
}
