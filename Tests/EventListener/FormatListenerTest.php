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

use FOS\RestBundle\Util\FormatNegotiator;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\EventListener\FormatListener;

/**
 * Request listener test.
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

    public function testOnKernelControllerNegotiationStopped()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        $request->setRequestFormat('xml');

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $formatNegotiator = new FormatNegotiator();
        $formatNegotiator->add(new RequestMatcher('/'), array('stop' => true));
        $formatNegotiator->add(new RequestMatcher('/'), array('fallback_format' => 'json'));

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

    /**
     * Test FormatListener won't overwrite request format when it was already specified.
     *
     * @dataProvider useSpecifiedFormatDataProvider
     */
    public function testUseSpecifiedFormat($format, $result)
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        if ($format) {
            $request->setRequestFormat($format);
        }

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $formatNegotiator = $this->getMockBuilder('FOS\RestBundle\Util\FormatNegotiator')
            ->disableOriginalConstructor()
            ->getMock();
        $formatNegotiator->expects($this->any())
            ->method('getBestMediaType')
            ->will($this->returnValue('application/xml'));

        $listener = new FormatListener($formatNegotiator);

        $listener->onKernelRequest($event);

        $this->assertEquals($request->getRequestFormat(), $result);
    }

    public function useSpecifiedFormatDataProvider()
    {
        return array(
            array(null, 'xml'),
            array('json', 'json'),
        );
    }

    /**
     * Generates a request like a symfony fragment listener does.
     * Set request type to master.
     */
    public function testSfFragmentFormat()
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        $attributes = array('_locale' => 'en', '_format' => 'json', '_controller' => 'FooBundle:Index:featured');
        $request->attributes->add($attributes);
        $request->attributes->set('_route_params', array_replace($request->attributes->get('_route_params', array()), $attributes));

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $formatNegotiator = $this->getMockBuilder('FOS\RestBundle\Util\FormatNegotiator')
            ->disableOriginalConstructor()
            ->getMock();
        $formatNegotiator->expects($this->any())
            ->method('getBestMediaType')
            ->will($this->returnValue('application/json'));

        $listener = new FormatListener($formatNegotiator);

        $listener->onKernelRequest($event);

        $this->assertEquals($request->getRequestFormat(), 'json');
    }
}
