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

use FOS\RestBundle\EventListener\ZoneMatcherListener;
use FOS\RestBundle\FOSRestBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class ZoneMatcherListenerTest extends TestCase
{
    public function testNoRequestMatcher()
    {
        $request = new Request();
        $event = $this->getGetResponseEvent($request);

        $listener = new ZoneMatcherListener();
        $listener->onKernelRequest($event);

        $this->assertTrue($request->attributes->has(FOSRestBundle::ZONE_ATTRIBUTE));
    }

    public function testWithRequestMatcherMatch()
    {
        $request = new Request();
        $event = $this->getGetResponseEvent($request);

        $requestMatcher = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestMatcherInterface')->getMock();
        $requestMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($request)
            ->will($this->returnValue(true));

        $listener = new ZoneMatcherListener();
        $listener->addRequestMatcher($requestMatcher);
        $listener->onKernelRequest($event);

        $this->assertTrue($request->attributes->has(FOSRestBundle::ZONE_ATTRIBUTE));
    }

    public function testWithRequestMatcherNoMatch()
    {
        $request = new Request();
        $event = $this->getGetResponseEvent($request);

        $requestMatcher = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestMatcherInterface')->getMock();
        $requestMatcher
            ->expects($this->once())
            ->method('matches')
            ->with($request)
            ->will($this->returnValue(false));

        $listener = new ZoneMatcherListener();
        $listener->addRequestMatcher($requestMatcher);
        $listener->onKernelRequest($event);

        $this->assertFalse($request->attributes->get(FOSRestBundle::ZONE_ATTRIBUTE));
    }

    private function getGetResponseEvent(Request $request)
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event
            ->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        return $event;
    }
}
