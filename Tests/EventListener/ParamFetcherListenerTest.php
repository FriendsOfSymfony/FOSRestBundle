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
use Symfony\Component\HttpFoundation\Request;

/**
 * Param Fetcher Listener Tests
 */
class ParamFetcherListenerTest extends \PHPUnit_Framework_TestCase
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
        $request = new Request;
        $request->attributes->set('customer', null);
        $event = $this->getEvent($request);

        $this->paramFetcher->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array(
                'customer' => 5
            )));

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
        $request = new Request;
        $event = $this->getEvent($request);

        $this->paramFetcher->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array()));

        $this->paramFetcherListener->onKernelController($event);

        $this->assertSame($this->paramFetcher, $request->attributes->get('paramFetcher'));
    }

    protected function getEvent(Request $request)
    {
        $event = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->atLeastOnce())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $controller = $this->getMockBuilder('Symfony\\Bundle\\FrameworkBundle\\Controller\\Controller')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->atLeastOnce())
            ->method('getController')
            ->will($this->returnValue(array($controller, 'somethingAction')));

        return $event;
    }

    public function setUp()
    {
        $this->paramFetcher = $this->getMockBuilder('FOS\\RestBundle\\Request\\ParamFetcher')
            ->disableOriginalConstructor()
            ->getMock();

        $this->paramFetcherListener = new ParamFetcherListener($this->paramFetcher, true);
    }
}
