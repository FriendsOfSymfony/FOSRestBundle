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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Version listener test.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class VersionListenerTest extends TestCase
{
    /**
     * @var \FOS\RestBundle\View\ConfigurableViewHandlerInterface
     */
    private $viewHandler;

    /**
     * @var \FOS\RestBundle\Version\VersionResolverInterface
     */
    private $resolver;

    /**
     * @var VersionListener
     */
    private $listener;

    public function setUp()
    {
        $this->viewHandler = $this->getMockBuilder('FOS\RestBundle\View\ConfigurableViewHandlerInterface')->getMock();
        $this->resolver = $this->getMockBuilder('FOS\RestBundle\Version\VersionResolverInterface')->getMock();

        $this->listener = new VersionListener($this->viewHandler, $this->resolver);
    }

    public function testMatchNoZone()
    {
        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);

        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseEvent')
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
