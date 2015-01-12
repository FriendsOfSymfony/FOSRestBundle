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

use FOS\RestBundle\Normalizer\Exception\NormalizationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\HeaderBag;
use FOS\RestBundle\Decoder\ContainerDecoderProvider;
use FOS\RestBundle\EventListener\BodyListener;

/**
 * Request listener test
 *
 * @author Alain Horner <alain.horner@liip.ch>
 * @author Stefan Paschke <stefan.paschke@liip.ch>
 */
class BodyListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param boolean $decode                                 use decoder provider
     * @param Request $request                                the original request
     * @param string  $method                                 a http method (e.g. POST, GET, PUT, ...)
     * @param array   $expectedParameters                     the http parameters of the updated request
     * @param string  $contentType                            the request header content type
     * @param boolean $throwExceptionOnUnsupportedContentType
     *
     * @dataProvider testOnKernelRequestDataProvider
     */
    public function testOnKernelRequest($decode, Request $request, $method, $expectedParameters, $contentType = null, $throwExceptionOnUnsupportedContentType = false)
    {
        $decoder = $this->getMockBuilder('FOS\RestBundle\Decoder\DecoderInterface')->disableOriginalConstructor()->getMock();
        $decoder->expects($this->any())
            ->method('decode')
            ->will($this->returnValue($request->getContent()));

        $decoderProvider = new ContainerDecoderProvider(array('json' => 'foo'));

        $listener = new BodyListener($decoderProvider, $throwExceptionOnUnsupportedContentType);

        if ($decode) {
            $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));
            $container
                ->expects($this->once())
                ->method('get')
                ->with('foo')
                ->will($this->returnValue($decoder));

            $decoderProvider->setContainer($container);
        }

        $request->setMethod($method);

        if ($contentType) {
            $request->headers = new HeaderBag(array('Content-Type' => $contentType));
        }

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener->onKernelRequest($event);

        $this->assertEquals($request->request->all(), $expectedParameters);
    }

    public static function testOnKernelRequestDataProvider()
    {
        return array(
            'Empty POST request' => array(true, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'POST', array('foo'), 'application/json'),
            'Empty PUT request' => array(true, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'PUT', array('foo'), 'application/json'),
            'Empty PATCH request' => array(true, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'PATCH', array('foo'), 'application/json'),
            'Empty DELETE request' => array(true, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'DELETE', array('foo'), 'application/json'),
            'Empty GET request' => array(false, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'GET', array(), 'application/json'),
            'POST request with parameters' => array(false, new Request(array(), array('bar'), array(), array(), array(), array('CONTENT_TYPE' => 'application/x-www-form-urlencoded'), array('foo')), 'POST', array('bar'), 'application/x-www-form-urlencoded'),
            'POST request with unallowed format' => array(false, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'POST', array(), 'application/fooformat'),
            'POST request with no Content-Type' => array(true, new Request(array(), array(), array('_format' => 'json'), array(), array(), array(), array('foo')), 'POST', array('foo')),
        );
    }

    public function testOnKernelRequestWithNormalizer()
    {
        $data = array('foo_bar' => 'foo_bar');
        $normalizedData = array('fooBar' => 'foo_bar');

        $decoder = $this->getMock('FOS\RestBundle\Decoder\DecoderInterface');
        $decoder
            ->expects($this->any())
            ->method('decode')
            ->will($this->returnValue($data));

        $decoderProvider = $this->getMock('FOS\RestBundle\Decoder\DecoderProviderInterface');
        $decoderProvider
            ->expects($this->any())
            ->method('getDecoder')
            ->will($this->returnValue($decoder));

        $decoderProvider
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true));

        $normalizer = $this->getMock('FOS\RestBundle\Normalizer\ArrayNormalizerInterface');
        $normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($data)
            ->will($this->returnValue($normalizedData));

        $request = new Request(array(), array(), array(), array(), array(), array(), 'foo');
        $request->setMethod('POST');

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener = new BodyListener($decoderProvider, false, $normalizer);
        $listener->onKernelRequest($event);

        $this->assertEquals($normalizedData, $request->request->all());
    }

    public function testOnKernelRequestNormalizationWithForms()
    {
        $data = array('foo_bar' => 'foo_bar');
        $normalizedData = array('fooBar' => 'foo_bar');
        $decoderProvider = $this->getMock('FOS\RestBundle\Decoder\DecoderProviderInterface');

        $normalizer = $this->getMock('FOS\RestBundle\Normalizer\ArrayNormalizerInterface');
        $normalizer
            ->expects($this->once())
            ->method('normalize')
            ->with($data)
            ->will($this->returnValue($normalizedData));

        $request = new Request(array(), $data, array(), array(), array(), array(), 'foo');
        $request->headers->set('Content-Type', 'multipart/form-data');
        $request->setMethod('POST');

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener = new BodyListener($decoderProvider, false, $normalizer, true);
        $listener->onKernelRequest($event);

        $this->assertEquals($normalizedData, $request->request->all());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     */
    public function testOnKernelRequestNormalizationException()
    {
        $decoder = $this->getMock('FOS\RestBundle\Decoder\DecoderInterface');
        $decoder
            ->expects($this->any())
            ->method('decode')
            ->will($this->returnValue(array()));

        $decoderProvider = $this->getMock('FOS\RestBundle\Decoder\DecoderProviderInterface');
        $decoderProvider
            ->expects($this->any())
            ->method('getDecoder')
            ->will($this->returnValue($decoder));

        $decoderProvider
            ->expects($this->any())
            ->method('supports')
            ->will($this->returnValue(true));

        $normalizer = $this->getMock('FOS\RestBundle\Normalizer\ArrayNormalizerInterface');
        $normalizer
            ->expects($this->once())
            ->method('normalize')
            ->will($this->throwException(new NormalizationException()));

        $request = new Request(array(), array(), array(), array(), array(), array(), 'foo');
        $request->setMethod('POST');

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $listener = new BodyListener($decoderProvider, false, $normalizer);
        $listener->onKernelRequest($event);
    }

    /**
     * Test that a malformed request will cause a BadRequestHttpException to be thrown
     */
    public function testBadRequestExceptionOnMalformedContent()
    {
        $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\BadRequestHttpException');
        $this->testOnKernelRequest(true, new Request(array(), array(), array(), array(), array(), array(), 'foo'), 'POST', array(), 'application/json');
    }

    /**
     * Test that a unallowed format will cause a UnsupportedMediaTypeHttpException to be thrown
     */
    public function testUnsupportedMediaTypeHttpExceptionOnUnsupportedMediaType()
    {
        $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException');
        $this->testOnKernelRequest(false, new Request(array(), array(), array(), array(), array(), array(), 'foo'), 'POST', array(), 'application/foo', true);
    }

    public function testShouldNotThrowUnsupportedMediaTypeHttpExceptionWhenIsAnEmptyDeleteRequest()
    {
        $this->testOnKernelRequest(false, new Request(), 'DELETE', array(), null, true);
    }
}
