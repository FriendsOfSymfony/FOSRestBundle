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

use FOS\RestBundle\EventListener\VersionExclusionListener;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\View\ConfigurableViewHandlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Version exclusion listener test.
 *
 * @author Alexandr Zolotukhin <alex@alexandrz.com>
 */
class VersionExclusionListenerTest extends TestCase
{
    public function testVersionIsNotSetWhenZoneIsFalse()
    {
        $version = 'v1';

        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);
        $request->attributes->set('version', $version);

        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $viewHandler = $this->getMockBuilder(ConfigurableViewHandlerInterface::class)->getMock();
        $viewHandler
            ->expects($this->never())
            ->method('setExclusionStrategyVersion');

        $listener = new VersionExclusionListener($viewHandler);

        $listener->onKernelRequest($event);
    }

    public function testVersionIsSet()
    {
        $version = 'v1';

        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, true);
        $request->attributes->set('version', $version);

        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $viewHandler = $this->getMockBuilder(ConfigurableViewHandlerInterface::class)->getMock();
        $viewHandler
            ->expects($this->once())
            ->method('setExclusionStrategyVersion')
            ->with($version);

        $listener = new VersionExclusionListener($viewHandler);

        $listener->onKernelRequest($event);
    }
}
