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

use FOS\RestBundle\EventListener\MimeTypeListener;
use FOS\RestBundle\FOSRestBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Request listener test.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class MimeTypeListenerTest extends TestCase
{
    public function testOnKernelRequest()
    {
        $listener = new MimeTypeListener(['jsonp' => ['application/javascript+jsonp']]);

        $request = new Request();
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->any())
              ->method('getRequest')
              ->will($this->returnValue($request));

        $this->assertNull($request->getMimeType('jsonp'));

        $listener->onKernelRequest($event);

        $this->assertNull($request->getMimeType('jsonp'));

        $event->expects($this->once())
              ->method('isMainRequest')
              ->will($this->returnValue(true));

        $listener->onKernelRequest($event);

        $this->assertEquals('application/javascript+jsonp', $request->getMimeType('jsonp'));
    }

    public function testOnKernelRequestNoZone()
    {
        $listener = new MimeTypeListener(['soap' => ['application/soap+xml']]);

        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->never())
            ->method('isMainRequest')
            ->will($this->returnValue(true));

        $listener->onKernelRequest($event);

        $this->assertNull($request->getMimeType('soap'));
    }

    public function testOnKernelRequestWithZone()
    {
        $listener = new MimeTypeListener(['soap' => ['application/soap+xml']]);

        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, true);
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('isMainRequest')
            ->will($this->returnValue(true));

        $listener->onKernelRequest($event);

        $this->assertEquals('application/soap+xml', $request->getMimeType('soap'));
    }
}
