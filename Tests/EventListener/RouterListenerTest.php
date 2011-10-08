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

use Symfony\Component\HttpKernel\HttpKernelInterface,
    Symfony\Component\HttpFoundation\Request;

use FOS\RestBundle\EventListener\RouterListener;

/**
 * Request listener test
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class RouterListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelRequestNegotiation()
    {
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $acceptHeaderNegotiator = $this->getMockBuilder('FOS\RestBundle\Util\AcceptHeaderNegotiator')->disableOriginalConstructor()->getMock();

        $listener = new RouterListener($acceptHeaderNegotiator, 'xml', array());

        $listener->onKernelRequest($event);

        $this->assertEquals($request->getRequestFormat(), 'xml');
    }

    public function testOnKernelRequestDefault()
    {
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $acceptHeaderNegotiator = $this->getMockBuilder('FOS\RestBundle\Util\AcceptHeaderNegotiator')->disableOriginalConstructor()->getMock();
        $acceptHeaderNegotiator->expects($this->once())
            ->method('getBestFormat')
            ->will($this->returnValue('xml'));

        $listener = new RouterListener($acceptHeaderNegotiator, null, array('json'));

        $listener->onKernelRequest($event);

        $this->assertEquals($request->getRequestFormat(), 'xml');
    }

    public function testOnKernelRequestNoFormat()
    {
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));

        $acceptHeaderNegotiator = $this->getMockBuilder('FOS\RestBundle\Util\AcceptHeaderNegotiator')->disableOriginalConstructor()->getMock();

        $listener = new RouterListener($acceptHeaderNegotiator, null, array());

        $listener->onKernelRequest($event);

        $this->assertEquals('html', $request->getRequestFormat());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testOnKernelRequestException()
    {
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseEvent')->disableOriginalConstructor()->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $acceptHeaderNegotiator = $this->getMockBuilder('FOS\RestBundle\Util\AcceptHeaderNegotiator')->disableOriginalConstructor()->getMock();

        $listener = new RouterListener($acceptHeaderNegotiator, null, array());

        $listener->onKernelRequest($event);
    }
}
