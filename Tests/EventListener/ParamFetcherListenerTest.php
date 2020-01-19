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

use FOS\RestBundle\EventListener\ParamFetcherListener;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Tests\Fixtures\Controller\ParamFetcherController;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Param Fetcher Listener Tests.
 */
class ParamFetcherListenerTest extends TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paramFetcher;

    /**
     * @var \FOS\RestBundle\EventListener\ParamFetcherListener
     */
    private $paramFetcherListener;

    /**
     * Tests the ParamFetcher being able to set an attribute on the request
     * when configured to do so and the attribute is specified as a null
     * default value.
     */
    public function testSettingAttributes()
    {
        $request = new Request();
        $request->attributes->set('customer', null);
        $event = $this->getEvent($request);

        $this->paramFetcher->expects($this->once())
            ->method('all')
            ->will($this->returnValue([
                'customer' => 5,
            ]));

        $this->paramFetcherListener->onKernelController($event);

        $this->assertEquals(5, $request->attributes->get('customer'), 'Listener set attribute as expected');
    }

    /**
     * Tests the ParamFetcher being able to set an attribute on the request
     * when configured to do so and the attribute is specified as a null
     * default value.
     */
    public function testSettingParamFetcherOnRequest()
    {
        $request = new Request();
        $event = $this->getEvent($request);

        $this->paramFetcher->expects($this->once())
            ->method('all')
            ->will($this->returnValue([]));

        $this->paramFetcherListener->onKernelController($event);

        $this->assertSame($this->paramFetcher, $request->attributes->get('paramFetcher'));
    }

    public function testParamFetcherOnRequestNoZone()
    {
        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);
        $event = $this->getEvent($request);

        $this->paramFetcher->expects($this->never())
            ->method('all')
            ->will($this->returnValue(array()));

        $this->paramFetcherListener->onKernelController($event);

        $this->assertNull($request->attributes->get('paramFetcher'));
    }

    /**
     * Tests that the ParamFetcher can be injected by the default name
     * ($paramFetcher) or by a different name if type-hinted.
     *
     * @dataProvider setParamFetcherByTypehintProvider
     */
    public function testSettingParamFetcherByTypehint($actionName, $expectedAttribute)
    {
        $request = new Request();

        $event = $this->getEvent($request, $actionName);

        $this->paramFetcher->expects($this->once())
            ->method('all')
            ->will($this->returnValue([]));

        $this->paramFetcherListener->onKernelController($event);

        $this->assertSame($this->paramFetcher, $request->attributes->get($expectedAttribute));
    }

    /**
     * Tests that the ParamFetcher can be injected in a invokable controller.
     */
    public function testSettingParamFetcherForInvokable()
    {
        $request = new Request();
        $event = $this->getEvent($request, null);

        $this->paramFetcher->expects($this->once())
            ->method('all')
            ->will($this->returnValue([]));

        $this->paramFetcherListener->onKernelController($event);

        $this->assertSame($this->paramFetcher, $request->attributes->get('pfInvokable'));
    }

    public function setParamFetcherByTypehintProvider()
    {
        return [
            // Without a typehint, the ParamFetcher should be injected as
            // $paramFetcher.
            ['byNameAction', 'paramFetcher'],

            // With a typehint, the ParamFetcher should be injected as whatever
            // the parameter name is.
            ['byTypeAction', 'pf'],

            // The user can typehint using ParamFetcherInterface, too.
            ['byInterfaceAction', 'pfi'],

            // If there is no controller argument for the ParamFetcher, it
            // should be injected as the default name.
            ['notProvidedAction', 'paramFetcher'],
        ];
    }

    protected function getEvent(Request $request, $actionMethod = 'byNameAction')
    {
        $controller = new ParamFetcherController();
        $callable = $actionMethod ? [$controller, $actionMethod] : $controller;
        $kernel = $this->createMock(HttpKernelInterface::class);
        $eventClass = class_exists(ControllerEvent::class) ? ControllerEvent::class : FilterControllerEvent::class;

        return new $eventClass($kernel, $callable, $request, null);
    }

    public function setUp()
    {
        $this->paramFetcher = $this->getMockBuilder('FOS\\RestBundle\\Request\\ParamFetcher')
            ->disableOriginalConstructor()
            ->getMock();
        $this->paramFetcherListener = new ParamFetcherListener($this->paramFetcher, true);
    }
}
