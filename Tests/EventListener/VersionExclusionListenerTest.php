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
use Symfony\Component\HttpFoundation\Request;

/**
 * Version exclusion listener test.
 *
 * @author juillerat <philippe.juillerat@filago.ch>
 */
class VersionExclusionListenerTest extends \PHPUnit_Framework_TestCase
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
     * @var VersionExclusionListener
     */
    private $listener;

    public function setUp()
    {
        $this->viewHandler = $this->getMockBuilder('FOS\RestBundle\View\ConfigurableViewHandlerInterface')->getMock();

        $this->listener = new VersionExclusionListener($this->viewHandler);
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
