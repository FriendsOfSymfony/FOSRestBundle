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

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\HeaderBag,
    FOS\RestBundle\EventListener\BodyListener;

/**
 * Request listener test
 *
 * @author Alain Horner <alain.horner@liip.ch>
 * @author Stefan Paschke <stefan.paschke@liip.ch>
 */
class BodyListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param Symfony\Component\HttpFoundation\Request $request the original request
     * @param string $method a http method (e.g. POST, GET, PUT, ...)
     * @param string $contentType the request header content type
     * @param array $expectedParameters the http parameters of the updated request
     *
     * @dataProvider testOnKernelRequestDataProvider
     */
    public function testOnKernelRequest($request, $method, $contentType, $expectedParameters)
    {
        $encoder = $this->getMockBuilder('Symfony\Component\Serializer\Encoder\DecoderInterface')->disableOriginalConstructor()->getMock();
        $encoder->expects($this->any())
              ->method('decode')
              ->will($this->returnValue($request->getContent()));

        $serializer = $this->getMockBuilder('Symfony\Component\Serializer\Serializer')->disableOriginalConstructor()->getMock();
        $serializer->expects($this->any())
              ->method('supportsDecoding')
              ->will($this->returnValue(true));
        $serializer->expects($this->any())
              ->method('getEncoder')
              ->will($this->returnValue($encoder));

        $listener = new BodyListener($serializer);

        $request->setMethod($method);
        $request->headers = new HeaderBag(array('Content-Type' => $contentType));
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
              ->method('getRequest')
              ->will($this->returnValue($request));

        $listener->onKernelRequest($event);

        $this->assertEquals($request->request->all(), $expectedParameters);
    }

    public static function testOnKernelRequestDataProvider()
    {
        return array(
           'Empty POST request' => array(new Request(array(), array(), array(), array(), array(), array(), 'foo'), 'POST', 'application/json', array('foo')),
           'Empty PUT request' => array(new Request(array(), array(), array(), array(), array(), array(), 'foo'), 'PUT', 'application/json', array('foo')),
           'Empty PATCH request' => array(new Request(array(), array(), array(), array(), array(), array(), 'foo'), 'PATCH', 'application/json', array('foo')),
           'Empty DELETE request' => array(new Request(array(), array(), array(), array(), array(), array(), 'foo'), 'DELETE', 'application/json', array('foo')),
           'Empty GET request' => array(new Request(array(), array(), array(), array(), array(), array(), 'foo'), 'GET', 'application/json', array()),
           'POST request with parameters' => array(new Request(array(), array('bar'), array(), array(), array(), array(), 'foo'), 'POST', 'application/json', array('bar')),
           'POST request with unallowed format' => array(new Request(array(), array(), array(), array(), array(), array(), 'foo'), 'POST', 'application/fooformat', array()),
           'POST request with no Content-Type' => array(new Request(array(), array(), array('_format' => 'json'), array(), array(), array(), 'foo'), 'POST', null, array('foo')),
        );
    }
    
}
