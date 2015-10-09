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

use FOS\RestBundle\Controller\Annotations\View as ViewAnnotation;
use FOS\RestBundle\EventListener\ViewResponseListener;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * View response listener test.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ViewResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \FOS\RestBundle\EventListener\ViewResponseListener
     */
    public $listener;

    /**
     * @var \Symfony\Component\DependencyInjection\Container|\PHPUnit_Framework_MockObject_MockObject
     */
    public $container;

    /**
     * @var \FOS\RestBundle\View\ViewHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public $viewHandler;

    /**
     * @var \Symfony\Bundle\FrameworkBundle\Templating\EngineInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public $templating;

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpKernel\Event\FilterControllerEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFilterEvent(Request $request)
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\FilterControllerEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->once())
              ->method('getRequest')
              ->will($this->returnValue($request));

        return $event;
    }

    /**
     * @param Request $request
     * @param mixed   $result
     *
     * @return \Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResponseEvent(Request $request, $result)
    {
        $event = $this->getMockBuilder('Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->atLeastOnce())
              ->method('getRequest')
              ->will($this->returnValue($request));
        $event->expects($this->any())
              ->method('getControllerResult')
              ->will($this->returnValue($result));

        return $event;
    }

    public function testOnKernelController()
    {
        $request = new Request();
        $request->attributes->set('_view', 'foo');
        $event = $this->getFilterEvent($request);

        $this->listener->onKernelController($event);

        $this->assertEquals('foo', $request->attributes->get('_template'));
    }

    public function testOnKernelControllerNoView()
    {
        $request = new Request();
        $event = $this->getFilterEvent($request);

        $this->listener->onKernelController($event);

        $this->assertNull($request->attributes->get('_template'));
    }

    public function testOnKernelView()
    {
        $template = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\TemplateReference')
            ->disableOriginalConstructor()
            ->getMock();
        $template->expects($this->once())
            ->method('set')
            ->with('format', null);

        $request = new Request();
        $request->attributes->set('_template_default_vars', array('foo', 'halli'));
        $request->attributes->set('foo', 'baz');
        $request->attributes->set('halli', 'galli');
        $request->attributes->set('_template', $template);
        $response = new Response();

        $view = $this->getMockBuilder('FOS\RestBundle\View\View')
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects($this->exactly(2))
            ->method('getFormat')
            ->will($this->onConsecutiveCalls(null, 'html'));

        $this->viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf('FOS\RestBundle\View\View'), $this->equalTo($request))
            ->will($this->returnValue($response));
        $this->viewHandler->expects($this->once())
            ->method('isFormatTemplating')
            ->with('html')
            ->will($this->returnValue(true));

        $event = $this->getResponseEvent($request, $view);
        $event->expects($this->once())
            ->method('setResponse');

        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('fos_rest.view_handler'))
            ->will($this->returnValue($this->viewHandler));

        $this->listener->onKernelView($event);
    }

    public function testOnKernelViewWhenControllerResultIsNotViewObject()
    {
        $request = new Request();

        $event = $this->getResponseEvent($request, array());
        $event->expects($this->never())
            ->method('setResponse');

        $this->assertEquals(array(), $this->listener->onKernelView($event));
    }

    /**
     * onKernelView falls back to FrameworkExtraBundles' onKernelView
     * when fos_rest.view_response_listener.force_view is false.
     */
    public function testOnKernelViewFallsBackToFrameworkExtraBundle()
    {
        $template = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\TemplateReference')
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        $request->attributes->set('_template', $template);

        $this->templating->expects($this->any())
            ->method('renderResponse')
            ->with($template, array())
            ->will($this->returnValue(new Response('output')));
        $this->templating->expects($this->any())
            ->method('render')
            ->with($template, array())
            ->will($this->returnValue('output'));

        $event = $this->getResponseEvent($request, array());
        $response = null;

        $event->expects($this->once())
            ->method('setResponse')
            ->will($this->returnCallback(function ($r) use (&$response) {
                $response = $r;
            }));

        $this->container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('templating'))
            ->will($this->returnValue($this->templating));

        $this->listener->onKernelView($event);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame('output', $response->getContent());
    }

    public static function statusCodeProvider()
    {
        return array(
            array(201, 200, 201),
            array(201, 404, 404),
            array(201, 500, 500),
        );
    }

    /**
     * @dataProvider statusCodeProvider
     */
    public function testStatusCode($annotationCode, $viewCode, $expectedCode)
    {
        $viewAnnotation = new ViewAnnotation(array());
        $viewAnnotation->setStatusCode($annotationCode);

        $request = new Request();
        $request->setRequestFormat('json');
        $request->attributes->set('_view', $viewAnnotation);

        $this->viewHandler = new ViewHandler(array('json' => true));
        $this->viewHandler->setContainer($this->container);

        // This is why we avoid container dependencies!
        $that = $this;
        $this->container->expects($this->exactly(2))
            ->method('get')
            ->with($this->logicalOr('fos_rest.view_handler', 'fos_rest.templating'))
            ->will($this->returnCallback(function ($service) use ($that) {
                return $service === 'fos_rest.view_handler' ?
                    $that->viewHandler :
                    $that->templating;
            }));

        $this->templating->expects($this->any())
            ->method('render')
            ->will($this->returnValue('foo'));

        $view = new View();
        $view->setStatusCode($viewCode);
        $view->setData('foo');

        $event = $this->getResponseEvent($request, $view);

        $response = new Response();
        $event->expects($this->any())
            ->method('setResponse')
            ->will($this->returnCallback(function ($r) use (&$response) {
                $response = $r;
            }));

        $this->listener->onKernelView($event);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame($expectedCode, $response->getStatusCode());
    }

    public static function serializerEnableMaxDepthChecksProvider()
    {
        return array(
            array(false, get_class(null)),
            array(true, 'JMS\Serializer\Exclusion\DepthExclusionStrategy'),
        );
    }

    /**
     * @dataProvider serializerEnableMaxDepthChecksProvider
     */
    public function testSerializerEnableMaxDepthChecks($enableMaxDepthChecks, $expectedClass)
    {
        $viewAnnotation = new ViewAnnotation(array());
        $viewAnnotation->setSerializerEnableMaxDepthChecks($enableMaxDepthChecks);

        $request = new Request();
        $request->setRequestFormat('json');
        $request->attributes->set('_view', $viewAnnotation);

        $this->viewHandler = new ViewHandler(array('json' => true));
        $this->viewHandler->setContainer($this->container);

        // This is why we avoid container dependencies!
        $that = $this;
        $this->container->expects($this->exactly(2))
            ->method('get')
            ->with($this->logicalOr('fos_rest.view_handler', 'fos_rest.templating'))
            ->will($this->returnCallback(function ($service) use ($that) {
                        return $service === 'fos_rest.view_handler' ?
                            $that->viewHandler :
                            $that->templating;
                    }));

        $this->templating->expects($this->any())
            ->method('render')
            ->will($this->returnValue('foo'));

        $view = new View();

        $event = $this->getResponseEvent($request, $view);

        $this->listener->onKernelView($event);

        $context = $view->getSerializationContext();
        $exclusionStrategy = $context->getExclusionStrategy();

        $this->assertEquals($expectedClass, get_class($exclusionStrategy));
    }

    public function getDataForDefaultVarsCopy()
    {
        return array(
            array(true, false, false),
            array(true, true, true),
            array(false, null, true),
        );
    }

    /**
     * @dataProvider getDataForDefaultVarsCopy
     */
    public function testViewWithNoCopyDefaultVars($createAnnotation, $populateDefaultVars, $shouldCopy)
    {
        $request = new Request();
        $request->attributes->set('_template_default_vars', array('customer'));
        $request->attributes->set('customer', 'A person goes here');
        $view = View::create();

        if ($createAnnotation) {
            $viewAnnotation = new ViewAnnotation(array());
            $viewAnnotation->setPopulateDefaultVars($populateDefaultVars);
            $request->attributes->set('_view', $viewAnnotation);
        }

        $event = $this->getResponseEvent($request, $view);

        $this->viewHandler = new ViewHandler(array('html' => true));
        $this->viewHandler->setContainer($this->container);

        // This is why we avoid container dependencies!
        $that = $this;
        $this->container->expects($this->exactly(2))
            ->method('get')
            ->with($this->logicalOr('fos_rest.view_handler', 'fos_rest.templating'))
            ->will($this->returnCallback(function ($service) use ($that) {
                return $service === 'fos_rest.view_handler' ?
                    $that->viewHandler :
                    $that->templating;
            }));

        $this->listener->onKernelView($event);

        $data = $view->getData();
        if ($shouldCopy) {
            $this->assertArrayHasKey('customer', $data);
            $this->assertEquals('A person goes here', $data['customer']);
        } else {
            $this->assertNull($data);
        }
    }

    protected function setUp()
    {
        $this->viewHandler = $this->getMock('FOS\RestBundle\View\ViewHandlerInterface');
        $this->templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        $this->listener = new ViewResponseListener($this->container);
    }
}
