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

use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;

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
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $formatNegotiator = $this->getMockBuilder('FOS\RestBundle\Util\FormatNegotiator')
            ->disableOriginalConstructor()
            ->getMock();
        $formatNegotiator->expects($this->once())
            ->method('getBestMediaType')
            ->will($this->returnValue('application/xml'));

        $listener = new FormatListener($formatNegotiator);

        $listener->onKernelRequest($event);

        $this->assertEquals($request->getRequestFormat(), 'xml');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testOnKernelControllerException()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $formatNegotiator = $this->getMockBuilder('FOS\RestBundle\Util\FormatNegotiator')
            ->disableOriginalConstructor()
            ->getMock();

        $listener = new FormatListener($formatNegotiator);

        $listener->onKernelRequest($event);
    }
}
