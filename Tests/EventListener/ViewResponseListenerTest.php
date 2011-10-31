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

use Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

use FOS\RestBundle\EventListener\ViewResponseListener;

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
        $template->expects($this->exactly(2))
              ->method('set');

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

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('getControllerResult')
            ->will($this->returnValue(array()));

        $event->expects($this->never())
            ->method('setResponse');

        $listener->onKernelView($event);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testOnKernelViewWithNoViewNoDataWithForce()
    {
        $request = new Request();
        $request->attributes->set('_template_vars', true);
        $request->attributes->set('_template_default_vars', array('foo', 'halli'));

        $viewHandler = $this->getMock('\FOS\RestBundle\View\ViewHandlerInterface');
        $viewHandler->expects($this->once())
            ->method('isFormatTemplating')
            ->with('html')
            ->will($this->returnValue(true));

        $container = $this->getMockBuilder('\Symfony\Component\DependencyInjection\Container')->disableOriginalConstructor()->getMock();
        $container->expects($this->once())
            ->method('get')
            ->with($this->equalTo('fos_rest.view_handler'))
            ->will($this->returnValue($viewHandler));

        $container->expects($this->once())
            ->method('getParameter')
            ->with('fos_rest.view_response_listener.force_view')
            ->will($this->returnValue(true));

        $listener = new ViewResponseListener($container);

        $event = $this->getMockBuilder('\Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent')->disableOriginalConstructor()->getMock();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('getControllerResult')
            ->will($this->returnValue('foo'));

        $event->expects($this->never())
            ->method('setResponse');

        $listener->onKernelView($event);
    }
}
