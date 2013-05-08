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

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\EventListener\ViewResponseListener;
use FOS\Rest\Util\Codes;

/**
 * View response listener test
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ViewResponseListenerTest extends \PHPUnit_Framework_TestCase
{
    public function testOnKernelController()
    {
        $request = new Request();
        $request->attributes->set('_view', 'foo');

        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterControllerEvent')->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
              ->method('getRequest')
              ->will($this->returnValue($request));

        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $listener = new ViewResponseListener($container);

        $listener->onKernelController($event);

        $this->assertEquals('foo', $request->attributes->get('_template'));
    }

    public function testOnKernelControllerNoView()
    {
        $request = new Request();

        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\FilterControllerEvent')->disableOriginalConstructor()->getMock();
        $event->expects($this->once())
              ->method('getRequest')
              ->will($this->returnValue($request));

        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $listener = new ViewResponseListener($container);

        $listener->onKernelController($event);

        $this->assertNull($request->attributes->get('_template'));
    }

    public function testOnKernelView()
    {
        $template = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Templating\TemplateReference')->disableOriginalConstructor()->getMock();
        $template->expects($this->once())
            ->method('set')
            ->with('format', null);

        $request = new Request();
        $request->attributes->set('_template_default_vars', array('foo', 'halli'));
        $request->attributes->set('foo', 'baz');
        $request->attributes->set('halli', 'galli');
        $request->attributes->set('_template', $template);

        $response = new Response();

        $view = $this->getMockBuilder('\FOS\RestBundle\View\View')->disableOriginalConstructor()->getMock();

        $view->expects($this->exactly(2))
        ->method('getFormat')
        ->will($this->onConsecutiveCalls(null, 'html'));

        $viewHandler = $this->getMock('\FOS\RestBundle\View\ViewHandlerInterface');
        $viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf('\FOS\RestBundle\View\View'), $this->equalTo($request))
            ->will($this->returnValue($response));
        $viewHandler->expects($this->once())
            ->method('isFormatTemplating')
            ->with('html')
            ->will($this->returnValue(true));

        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('fos_rest.view_handler'))
            ->will($this->returnValue($viewHandler));

        $listener = new ViewResponseListener($container);

        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')->disableOriginalConstructor()->getMock();

        $event->expects($this->once())
              ->method('getRequest')
              ->will($this->returnValue($request));

        $event->expects($this->once())
              ->method('getControllerResult')
              ->will($this->returnValue($view));

        $event->expects($this->once())
              ->method('setResponse');

        $listener->onKernelView($event);
    }

    public function testOnKernelViewWhenControllerResultIsNotViewObject()
    {
        $request = new Request();

        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $listener = new ViewResponseListener($container);

        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')->disableOriginalConstructor()->getMock();

        $event->expects($this->exactly(2))
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->exactly(2))
            ->method('getControllerResult')
            ->will($this->returnValue(array()));

        $event->expects($this->never())
            ->method('setResponse');

        $listener->onKernelView($event);
    }

    /**
     * onKernelView falls back to FrameworkExtraBundles' onKernelView
     * when fos_rest.view_response_listener.force_view is false
     */
    public function testOnKernelViewFallsBackToFrameworkExtraBundle()
    {
        $template = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Templating\TemplateReference')->disableOriginalConstructor()->getMock();

        $request = new Request();
        $request->attributes->set('_template', $template);

        $templating = $this->getMock('\Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $templating->expects($this->any())
            ->method('renderResponse')
            ->with($template, array())
            ->will($this->returnValue(new Response('output')));
        $templating->expects($this->any())
            ->method('render')
            ->with($template, array())
            ->will($this->returnValue('output'));

        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->once())
            ->method('get')
            ->with('templating')
            ->will($this->returnValue($templating));

        $listener = new ViewResponseListener($container);

        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')->disableOriginalConstructor()->getMock();

        $event->expects($this->any())
              ->method('getRequest')
              ->will($this->returnValue($request));

        $event->expects($this->any())
              ->method('getControllerResult')
              ->will($this->returnValue(array()));

        $response = null;

        $event->expects($this->once())
            ->method('setResponse')
            ->will($this->returnCallback(function ($r) use (&$response) {
                $response = $r;
            }));

        $listener->onKernelView($event);

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
        $viewAnnotation = new \FOS\RestBundle\Controller\Annotations\View(array());
        $viewAnnotation->setStatusCode($annotationCode);

        $request = new Request();
        $request->setRequestFormat('json');
        $request->attributes->set('_view', $viewAnnotation);

        $viewHandler = new \FOS\RestBundle\View\ViewHandler(array('json' => true));
        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container');
        $viewHandler->setContainer($container);
        $container->expects($this->at(0))
            ->method('get')
            ->with($this->equalTo('fos_rest.view_handler'))
            ->will($this->returnValue($viewHandler));

        $templating = $this->getMock('\Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');
        $container->expects($this->at(1))
            ->method('get')
            ->with($this->equalTo('fos_rest.templating'))
            ->will($this->returnValue($templating));
        $templating->expects($this->any())
            ->method('render')
            ->will($this->returnValue('foo'));

        $listener = new ViewResponseListener($container);
        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')->disableOriginalConstructor()->getMock();
        $event->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($request));

       $view = new \FOS\RestBundle\View\View();
       $view->setStatusCode($viewCode);
       $view->setData('foo');

       $event->expects($this->any())
            ->method('getControllerResult')
            ->will($this->returnValue($view));

        $response = new Response();
        $event->expects($this->any())
            ->method('setResponse')
            ->will($this->returnCallback(function ($r) use (&$response) {
                $response = $r;
            }));

        $listener->onKernelView($event);

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame($expectedCode, $response->getStatusCode());
    }
}
