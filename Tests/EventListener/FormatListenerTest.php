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

use FOS\RestBundle\EventListener\FormatListener;

/**
 * Request listener test
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class FormatListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelControllerNegotiation()
    {
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterControllerEvent')->disableOriginalConstructor()->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $formatNegotiator = $this->getMockBuilder('FOS\Rest\Util\FormatNegotiator')->disableOriginalConstructor()->getMock();

        $listener = new FormatListener($formatNegotiator, 'xml', array());

        $listener->onKernelController($event);

        $this->assertEquals($request->getRequestFormat(), 'xml');
    }

    public function testOnKernelControllerDefault()
    {
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterControllerEvent')->disableOriginalConstructor()->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $formatNegotiator = $this->getMockBuilder('FOS\Rest\Util\FormatNegotiator')->disableOriginalConstructor()->getMock();
        $formatNegotiator->expects($this->once())
            ->method('getBestFormat')
            ->will($this->returnValue('xml'));

        $listener = new FormatListener($formatNegotiator, null, array('json'));

        $listener->onKernelController($event);

        $this->assertEquals($request->getRequestFormat(), 'xml');
    }

    public function testOnKernelControllerNoFormat()
    {
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterControllerEvent')->disableOriginalConstructor()->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::SUB_REQUEST));

        $formatNegotiator = $this->getMockBuilder('FOS\Rest\Util\FormatNegotiator')->disableOriginalConstructor()->getMock();

        $listener = new FormatListener($formatNegotiator, null, array());

        $listener->onKernelController($event);

        $this->assertEquals('html', $request->getRequestFormat());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testOnKernelControllerException()
    {
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterControllerEvent')->disableOriginalConstructor()->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $formatNegotiator = $this->getMockBuilder('FOS\Rest\Util\FormatNegotiator')->disableOriginalConstructor()->getMock();

        $listener = new FormatListener($formatNegotiator, null, array());

        $listener->onKernelController($event);
    }
}
