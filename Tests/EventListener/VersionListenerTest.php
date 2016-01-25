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
use Symfony\Component\HttpFoundation\Request;

/**
 * Version listener test.
 *
 * @author Ener-Getick <egetick@gmail.com>
 */
class VersionListenerTest extends \PHPUnit_Framework_TestCase
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
     * @var \FOS\RestBundle\EventListener\VersionListener
     */
    private $listener;

    public function setUp()
    {
        $this->viewHandler = $this->getMock('FOS\RestBundle\View\ConfigurableViewHandlerInterface');
        $this->resolver = $this->getMock('FOS\RestBundle\Version\VersionResolverInterface');

        $this->listener = new VersionListener($this->viewHandler, $this->resolver);
    }

    public function testDefaultVersion()
    {
        $this->assertEquals(false, $this->listener->getVersion());
    }

    public function testMatchNoZone()
    {
        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);

        $event = $this->getMock('Symfony\Component\HttpKernel\Event\GetResponseEvent', [], [], '', false);
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->listener->onKernelRequest($event);

        $this->assertFalse($this->listener->getVersion());
    }
}
