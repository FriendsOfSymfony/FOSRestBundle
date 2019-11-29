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
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Twig\Environment;

/**
 * View response listener test.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ViewResponseListenerTest extends TestCase
{
    /**
     * @var \FOS\RestBundle\EventListener\ViewResponseListener
     */
    public $listener;

    /**
     * @var \FOS\RestBundle\View\ViewHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public $viewHandler;

    /**
     * @var Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    public $templating;

    private $router;
    private $serializer;
    private $requestStack;

    /**
     * @param Request $request
     *
     * @return \Symfony\Component\HttpKernel\Event\ControllerEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFilterEvent(Request $request)
    {
        $controller = new FooController();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $eventClass = class_exists(ControllerEvent::class) ? ControllerEvent::class : FilterControllerEvent::class;

        return new $eventClass($kernel, [$controller, 'viewAction'], $request, null);
    }

    /**
     * @param Request $request
     * @param mixed   $result
     *
     * @return \Symfony\Component\HttpKernel\Event\ViewEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResponseEvent(Request $request, $result)
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $eventClass = class_exists(ViewEvent::class) ? ViewEvent::class : GetResponseForControllerResultEvent::class;

        return new $eventClass($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $result);
    }

    public function testOnKernelView()
    {
        $template = $this->getMockBuilder('Symfony\Component\Templating\TemplateReferenceInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $template->expects($this->once())
            ->method('set')
            ->with('format', null);

        $annotation = new ViewAnnotation([]);
        $annotation->setOwner([new FooController(), 'onKernelViewAction']);
        $annotation->setTemplate($template);

        $request = new Request();
        $request->attributes->set('foo', 'baz');
        $request->attributes->set('halli', 'galli');
        $request->attributes->set('_template', $annotation);
        $response = new Response();

        $view = $this->getMockBuilder('FOS\RestBundle\View\View')
            ->disableOriginalConstructor()
            ->getMock();
        $view->expects($this->exactly(2))
            ->method('getFormat')
            ->will($this->onConsecutiveCalls(null, 'html'));

        $viewHandler = $this->getMockBuilder('FOS\RestBundle\View\ViewHandlerInterface')->getMock();
        $viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf('FOS\RestBundle\View\View'), $this->equalTo($request))
            ->will($this->returnValue($response));
        $viewHandler->expects($this->once())
            ->method('isFormatTemplating')
            ->with('html')
            ->will($this->returnValue(true));

        $this->listener = new ViewResponseListener($viewHandler, false);

        $event = $this->getResponseEvent($request, $view);
        $this->listener->onKernelView($event);

        $this->assertNotNull($event->getResponse());
    }

    public function testOnKernelViewWhenControllerResultIsNotViewObject()
    {
        $this->createViewResponseListener();

        $request = new Request();
        $event = $this->getResponseEvent($request, []);

        $this->assertNull($this->listener->onKernelView($event));
        $this->assertNull($event->getResponse());
    }

    public static function statusCodeProvider()
    {
        return [
            [201, 200, 201],
            [201, 404, 404],
            [201, 500, 500],
        ];
    }

    /**
     * @dataProvider statusCodeProvider
     */
    public function testStatusCode($annotationCode, $viewCode, $expectedCode)
    {
        $this->createViewResponseListener(['json' => true]);

        $viewAnnotation = new ViewAnnotation([]);
        $viewAnnotation->setOwner([$this, 'statusCodeProvider']);
        $viewAnnotation->setStatusCode($annotationCode);

        $request = new Request();
        $request->setRequestFormat('json');
        $request->attributes->set('_template', $viewAnnotation);

        $this->templating->expects($this->any())
            ->method('render')
            ->will($this->returnValue('foo'));

        $view = new View();
        $view->setStatusCode($viewCode);
        $view->setData('foo');

        $event = $this->getResponseEvent($request, $view);
        $this->listener->onKernelView($event);
        $response = $event->getResponse();

        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertSame($expectedCode, $response->getStatusCode());
    }

    public static function serializerEnableMaxDepthChecksProvider()
    {
        return [
            [false, null],
            [true, 0],
        ];
    }

    /**
     * @dataProvider serializerEnableMaxDepthChecksProvider
     */
    public function testSerializerEnableMaxDepthChecks($enableMaxDepthChecks, $expectedMaxDepth)
    {
        $this->createViewResponseListener(['json' => true]);

        $viewAnnotation = new ViewAnnotation([]);
        $viewAnnotation->setOwner([$this, 'testSerializerEnableMaxDepthChecks']);
        $viewAnnotation->setSerializerEnableMaxDepthChecks($enableMaxDepthChecks);

        $request = new Request();
        $request->setRequestFormat('json');
        $request->attributes->set('_template', $viewAnnotation);

        $this->templating->expects($this->any())
            ->method('render')
            ->will($this->returnValue('foo'));

        $view = new View();

        $event = $this->getResponseEvent($request, $view);

        $this->listener->onKernelView($event);

        $context = $view->getContext();

        $this->assertEquals($expectedMaxDepth, $context->getMaxDepth(false));
        $this->assertEquals($enableMaxDepthChecks, $context->isMaxDepthEnabled());
    }

    public function getDataForDefaultVarsCopy()
    {
        return [
            [false],
            [true],
        ];
    }

    /**
     * @dataProvider getDataForDefaultVarsCopy
     */
    public function testViewWithNoCopyDefaultVars($populateDefaultVars)
    {
        $this->createViewResponseListener(['html' => true]);

        $request = new Request();
        $request->attributes->set('customer', 'A person goes here');
        $view = View::create();

        $viewAnnotation = new ViewAnnotation([]);
        $viewAnnotation->setOwner([new FooController(), 'viewAction']);
        $viewAnnotation->setPopulateDefaultVars($populateDefaultVars);
        $request->attributes->set('_template', $viewAnnotation);

        $event = $this->getResponseEvent($request, $view);

        $this->listener->onKernelView($event);

        $data = $view->getData();
        if ($populateDefaultVars) {
            $this->assertArrayHasKey('customer', $data);
            $this->assertEquals('A person goes here', $data['customer']);
        } else {
            $this->assertNull($data);
        }
    }

    protected function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
        $this->serializer = $this->getMockBuilder('FOS\RestBundle\Serializer\Serializer')->getMock();
        $this->templating = $this->getMockBuilder(Environment::class)->disableOriginalConstructor()->getMock();
        $this->requestStack = new RequestStack();
    }

    private function createViewResponseListener($formats = null)
    {
        $this->viewHandler = new ViewHandler($this->router, $this->serializer, $this->templating, $this->requestStack, $formats);
        $this->listener = new ViewResponseListener($this->viewHandler, false);
    }
}

class FooController
{
    /**
     * @see testOnKernelView()
     */
    public function onKernelViewAction($foo, $halli)
    {
    }

    /**
     * @see testViewWithNoCopyDefaultVars()
     */
    public function viewAction($customer)
    {
    }
}
