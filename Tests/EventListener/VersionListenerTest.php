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

use FOS\RestBundle\EventListener\VersionListener;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Version\VersionResolverInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

/**
 * Version listener test.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class VersionListenerTest extends TestCase
{
    /**
     * @var VersionResolverInterface
     */
    private $resolver;

    /**
     * @var VersionListener
     */
    private $listener;

    protected function setUp(): void
    {
        $this->resolver = $this->getMockBuilder(VersionResolverInterface::class)->getMock();

        $this->listener = new VersionListener($this->resolver);
    }

    public function testMatchNoZone()
    {
        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);

        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->listener->onKernelRequest($event);

        $this->assertFalse($request->attributes->has('version'));
    }
}
