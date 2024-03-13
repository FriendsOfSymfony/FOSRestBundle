<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\EventListener;

use FOS\RestBundle\Decoder\ContainerDecoderProvider;
use FOS\RestBundle\Decoder\DecoderInterface;
use FOS\RestBundle\Decoder\DecoderProviderInterface;
use FOS\RestBundle\Decoder\JsonDecoder;
use FOS\RestBundle\EventListener\BodyListener;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Normalizer\ArrayNormalizerInterface;
use FOS\RestBundle\Normalizer\Exception\NormalizationException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException;

/**
 * Request listener test.
 *
 * @author Alain Horner <alain.horner@liip.ch>
 * @author Stefan Paschke <stefan.paschke@liip.ch>
 */
class BodyListenerTest extends TestCase
{
    /**
     * @param bool    $decode                                 use decoder provider
     * @param Request $request                                the original request
     * @param string  $method                                 a http method (e.g. POST, GET, PUT, ...)
     * @param array   $expectedParameters                     the http parameters of the updated request
     * @param string  $contentType                            the request header content type
     * @param bool    $throwExceptionOnUnsupportedContentType
     *
     * @dataProvider onKernelRequestDataProvider
     */
    public function testOnKernelRequest(bool $decode, Request $request, string $method, array $expectedParameters, string $contentType = null, $throwExceptionOnUnsupportedContentType = false): void
    {
        $decoder = $this->getMockBuilder(DecoderInterface::class)->getMock();
        $decoder->expects($this->any())
            ->method('decode')
            ->will($this->returnValue($request->getContent()));

        $container = new ContainerBuilder();
        $decoderProvider = new ContainerDecoderProvider($container, ['json' => 'json_decoder']);

        $listener = new BodyListener($decoderProvider, $throwExceptionOnUnsupportedContentType);

        if ($decode) {
            $container->set('json_decoder', new JsonDecoder());
        }

        $request->setMethod($method);

        if ($contentType) {
            $request->headers = new HeaderBag(['Content-Type' => $contentType]);
        }

        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener->onKernelRequest($event);

        $this->assertEquals($request->request->all(), $expectedParameters);
    }

    public static function onKernelRequestDataProvider(): array
    {
        return [
            'Empty POST request' => [true, new Request([], [], [], [], [], [], '["foo"]'), 'POST', ['foo'], 'application/json'],
            'Empty PUT request' => [true, new Request([], [], [], [], [], [], '["foo"]'), 'PUT', ['foo'], 'application/json'],
            'Empty PATCH request' => [true, new Request([], [], [], [], [], [], '["foo"]'), 'PATCH', ['foo'], 'application/json'],
            'Empty DELETE request' => [true, new Request([], [], [], [], [], [], '["foo"]'), 'DELETE', ['foo'], 'application/json'],
            'Empty GET request' => [false, new Request([], [], [], [], [], [], '["foo"]'), 'GET', [], 'application/json'],
            'POST request with parameters' => [false, new Request([], ['bar'], [], [], [], ['CONTENT_TYPE' => 'application/x-www-form-urlencoded'], ['foo']), 'POST', ['bar'], 'application/x-www-form-urlencoded'],
            'POST request with unallowed format' => [false, new Request([], [], [], [], [], [], 'foo'), 'POST', [], 'application/fooformat'],
            'POST request with no Content-Type' => [true, new Request([], [], ['_format' => 'json'], [], [], [], '["foo"]'), 'POST', ['foo']],
        ];
    }

    public function testOnKernelRequestNoZone(): void
    {
        $data = ['foo_bar' => 'foo_bar'];
        $normalizedData = ['fooBar' => 'foo_bar'];

        $decoder = $this->getMockBuilder(DecoderInterface::class)->getMock();
        $decoder
            ->expects($this->never())
            ->method('decode')
            ->will($this->returnValue($data));

        $decoderProvider = $this->getMockBuilder(DecoderProviderInterface::class)->getMock();
        $decoderProvider
            ->expects($this->never())
            ->method('getDecoder')
            ->will($this->returnValue($decoder));

        $normalizer = $this->getMockBuilder(ArrayNormalizerInterface::class)->getMock();
        $normalizer
            ->expects($this->never())
            ->method('normalize')
            ->with($data)
            ->will($this->returnValue($normalizedData));

        $request = new Request([], [], [], [], [], [], 'foo');
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);
        $request->setMethod('POST');

        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener = new BodyListener($decoderProvider, false, $normalizer);
        $listener->onKernelRequest($event);

        $this->assertEquals([], $request->request->all());
    }

    public function testOnKernelRequestWithNormalizer(): void
    {
        $data = ['foo_bar' => 'foo_bar'];
        $normalizedData = ['fooBar' => 'foo_bar'];

        $decoder = $this->getMockBuilder(DecoderInterface::class)->getMock();
        $decoder
            ->expects($this->any())
            ->method('decode')
            ->will($this->returnValue($data));

        $decoderProvider = $this->getMockBuilder(DecoderProviderInterface::class)->getMock();
        $decoderProvider
            ->expects($this->any())
            ->method('getDecoder')
            ->will($this->returnValue($decoder));

        $decoderProvider
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true));

        $normalizer = $this->getMockBuilder(ArrayNormalizerInterface::class)->getMock();
        $normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($data)
            ->will($this->returnValue($normalizedData));

        $request = new Request([], [], [], [], [], [], 'foo');
        $request->setMethod('POST');

        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener = new BodyListener($decoderProvider, false, $normalizer);
        $listener->onKernelRequest($event);

        $this->assertEquals($normalizedData, $request->request->all());
    }

    /**
     * @dataProvider formNormalizationProvider
     */
    public function testOnKernelRequestNormalizationWithForms(string $method, string|array|null $contentType, $mustBeNormalized): void
    {
        $data = ['foo_bar' => 'foo_bar'];
        $normalizedData = ['fooBar' => 'foo_bar'];
        $decoderProvider = $this->getMockBuilder(DecoderProviderInterface::class)->getMock();

        $normalizer = $this->getMockBuilder(ArrayNormalizerInterface::class)->getMock();

        if ($mustBeNormalized) {
            $normalizer
                ->expects($this->once())
                ->method('normalize')
                ->with($data)
                ->will($this->returnValue($normalizedData));
        } else {
            $normalizer
                ->expects($this->never())
                ->method('normalize');
        }

        $request = new Request([], $data, [], [], [], [], 'foo');
        $request->headers->set('Content-Type', $contentType);
        $request->setMethod($method);

        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener = new BodyListener($decoderProvider, false, $normalizer, true);
        $listener->onKernelRequest($event);

        if ($mustBeNormalized) {
            $this->assertEquals($normalizedData, $request->request->all());
        } else {
            $this->assertEquals($data, $request->request->all());
        }
    }

    public function formNormalizationProvider(): array
    {
        $cases = [];

        foreach (['POST', 'PUT', 'PATCH', 'DELETE'] as $method) {
            $cases[] = [$method, 'multipart/form-data', true];
            $cases[] = [$method, 'multipart/form-data; boundary=AaB03x', true];
            $cases[] = [$method, 'application/x-www-form-urlencoded', true];
            $cases[] = [$method, 'application/x-www-form-urlencoded; charset=utf-8', true];
            $cases[] = [$method, 'unknown', false];
        }

        return $cases;
    }

    public function testOnKernelRequestNormalizationException(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $decoder = $this->getMockBuilder(\FOS\RestBundle\Decoder\DecoderInterface::class)->getMock();
        $decoder
            ->expects($this->any())
            ->method('decode')
            ->will($this->returnValue([]));

        $decoderProvider = $this->getMockBuilder(DecoderProviderInterface::class)->getMock();
        $decoderProvider
            ->expects($this->any())
            ->method('getDecoder')
            ->will($this->returnValue($decoder));

        $decoderProvider
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true));

        $normalizer = $this->getMockBuilder(ArrayNormalizerInterface::class)->getMock();
        $normalizer
            ->expects($this->once())
            ->method('normalize')
            ->will($this->throwException(new NormalizationException()));

        $request = new Request([], [], [], [], [], [], 'foo');
        $request->setMethod('POST');

        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener = new BodyListener($decoderProvider, false, $normalizer);
        $listener->onKernelRequest($event);
    }

    /**
     * Test that a malformed request will cause a BadRequestHttpException to be thrown.
     */
    public function testBadRequestExceptionOnMalformedContent(): void
    {
        $this->expectException(BadRequestHttpException::class);

        $this->testOnKernelRequest(true, new Request([], [], [], [], [], [], 'foo'), 'POST', [], 'application/json');
    }

    /**
     * Test that a unallowed format will cause a UnsupportedMediaTypeHttpException to be thrown.
     */
    public function testUnsupportedMediaTypeHttpExceptionOnUnsupportedMediaType(): void
    {
        $this->expectException(UnsupportedMediaTypeHttpException::class);

        $this->testOnKernelRequest(false, new Request([], [], [], [], [], [], 'foo'), 'POST', [], 'application/foo', true);
    }

    public function testShouldNotThrowUnsupportedMediaTypeHttpExceptionWhenIsAnEmptyDeleteRequest(): void
    {
        $this->testOnKernelRequest(false, new Request(), 'DELETE', [], null, true);
    }
}
