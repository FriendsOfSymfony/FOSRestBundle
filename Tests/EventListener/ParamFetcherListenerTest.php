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
use FOS\RestBundle\Tests\Fixtures\Controller\ParamFetcherController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Param Fetcher Listener Tests
 */
class ParamFetcherListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $container;

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
            ->will($this->returnValue(array(
                'customer' => 5,
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
        $request = new Request();
        $event = $this->getEvent($request);

        $this->paramFetcher->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array()));

        $this->paramFetcherListener->onKernelController($event);

        $this->assertSame($this->paramFetcher, $request->attributes->get('paramFetcher'));
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
            ->will($this->returnValue(array()));

        $this->paramFetcherListener->onKernelController($event);

        $this->assertSame($this->paramFetcher, $request->attributes->get($expectedAttribute));
    }

    /**
     * Tests that the ParamFetcher can be injected in a invokable controller.
     */
    public function testSettingParamFetcherForInvokable()
    {
        $request = new Request();

        $event = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->atLeastOnce())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $controller = new ParamFetcherController();

        $event->expects($this->atLeastOnce())
            ->method('getController')
            ->will($this->returnValue($controller));

        $this->paramFetcher->expects($this->once())
            ->method('all')
            ->will($this->returnValue(array()));

        $this->paramFetcherListener->onKernelController($event);

        $this->assertSame($this->paramFetcher, $request->attributes->get('pfInvokable'));
    }

    public function setParamFetcherByTypehintProvider()
    {
        return array(
            // Without a typehint, the ParamFetcher should be injected as
            // $paramFetcher.
            array('byNameAction', 'paramFetcher'),

            // With a typehint, the ParamFetcher should be injected as whatever
            // the parameter name is.
            array('byTypeAction', 'pf'),

            // The user can typehint using ParamFetcherInterface, too.
            array('byInterfaceAction', 'pfi'),

            // If there is no controller argument for the ParamFetcher, it
            // should be injected as the default name.
            array('notProvidedAction', 'paramFetcher'),
        );
    }

    protected function getEvent(Request $request, $actionMethod = 'byNameAction')
    {
        $event = $this->getMockBuilder('Symfony\\Component\\HttpKernel\\Event\\FilterControllerEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->atLeastOnce())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $controller = new ParamFetcherController();

        $event->expects($this->atLeastOnce())
            ->method('getController')
            ->will($this->returnValue(array($controller, $actionMethod)));

        return $event;
    }

    public function setUp()
    {
        $this->container = $this->getMockBuilder('Symfony\\Component\\DependencyInjection\\ContainerInterface')
            ->getMock();

        $this->paramFetcher = $this->getMockBuilder('FOS\\RestBundle\\Request\\ParamFetcher')
            ->disableOriginalConstructor()
            ->getMock();

        $this->container->expects($this->once())
            ->method('get')
            ->with('fos_rest.request.param_fetcher')
            ->will($this->returnValue($this->paramFetcher));

        $this->paramFetcherListener = new ParamFetcherListener($this->container, true);
    }
}
