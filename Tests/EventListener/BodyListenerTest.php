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
     * @param boolean $decode             use decoder provider
     * @param Request $request            the original request
     * @param string  $method             a http method (e.g. POST, GET, PUT, ...)
     * @param string  $contentType        the request header content type
     * @param array   $expectedParameters the http parameters of the updated request
     * @param boolean $throwExceptionOnUnsupportedContentType
     *
     * @dataProvider testOnKernelRequestDataProvider
     */
    public function testOnKernelRequest($decode, $request, $method, $contentType, $expectedParameters, $throwExceptionOnUnsupportedContentType = false)
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
        $request->headers = new HeaderBag(array('Content-Type' => $contentType));
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
            'Empty POST request' => array(true, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'POST', 'application/json', array('foo')),
            'Empty PUT request' => array(true, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'PUT', 'application/json', array('foo')),
            'Empty PATCH request' => array(true, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'PATCH', 'application/json', array('foo')),
            'Empty DELETE request' => array(true, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'DELETE', 'application/json', array('foo')),
            'Empty GET request' => array(false, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'GET', 'application/json', array()),
            'POST request with parameters' => array(false, new Request(array(), array('bar'), array(), array(), array(), array(), array('foo')), 'POST', 'application/json', array('bar')),
            'POST request with unallowed format' => array(false, new Request(array(), array(), array(), array(), array(), array(), array('foo')), 'POST', 'application/fooformat', array()),
            'POST request with no Content-Type' => array(true, new Request(array(), array(), array('_format' => 'json'), array(), array(), array(), array('foo')), 'POST', null, array('foo'))
        );
    }

    /**
     * Test that a malformed request will cause a BadRequestHttpException to be thrown
     */
    public function testBadRequestExceptionOnMalformedContent()
    {
        $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\BadRequestHttpException');
        $this->testOnKernelRequest(true, new Request(array(), array(), array(), array(), array(), array(), 'foo'), 'POST', 'application/json', array());
    }

    /**
     * Test that a unallowed format will cause a UnsupportedMediaTypeHttpException to be thrown
     */
    public function testUnsupportedMediaTypeHttpExceptionOnUnsupportedMediaType()
    {
        $this->setExpectedException('\Symfony\Component\HttpKernel\Exception\UnsupportedMediaTypeHttpException');
        $this->testOnKernelRequest(false, new Request(array(), array(), array(), array(), array(), array(), 'foo'), 'POST', 'application/foo', array(), true);
    }

}
