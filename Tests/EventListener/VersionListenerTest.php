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

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;

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
     * @var \FOS\RestBundle\EventListener\VersionListener
     */
    private $listener;

    public function setUp()
    {
        $this->viewHandler = $this->getMock('FOS\RestBundle\View\ConfigurableViewHandlerInterface');

        $this->listener = $this->getMock('FOS\RestBundle\EventListener\VersionListener', null, [$this->viewHandler]);
    }

    public function testDefaultVersion()
    {
        $this->assertEquals(false, $this->listener->getVersion());
    }

    public function testMatch()
    {
        $this->listener->setRegex('/(v|version)=(?P<version>[0-9\.]+)/');

        $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $attributesBag = $this->getMock('Symfony\Component\HttpFoundation\ParameterBag');
        $attributesBag
            ->expects($this->once())
            ->method('get')
            ->with('media_type')
            ->willReturn('application/json/v=1.2');

        $request->attributes = $attributesBag;

        $event = $this->getMock('Symfony\Component\HttpKernel\Event\GetResponseEvent', [], [], '', false);
        $event
            ->expects($this->once())
            ->method('getRequest')
            ->willReturn($request);

        $this->listener->onKernelRequest($event);

        $this->assertEquals('1.2', $this->listener->getVersion());
    }
}
