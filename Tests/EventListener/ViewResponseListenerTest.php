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
use FOS\RestBundle\Serializer\Serializer;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\ViewHandlerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * View response listener test.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class ViewResponseListenerTest extends TestCase
{
    /**
     * @var ViewResponseListener
     */
    public $listener;

    /**
     * @var ViewHandlerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public $viewHandler;

    private $router;
    private $serializer;
    private $requestStack;

    /**
     * @param Request $request
     *
     * @return ControllerEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFilterEvent(Request $request)
    {
        $controller = new FooController();
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ControllerEvent($kernel, [$controller, 'viewAction'], $request, null);
    }

    /**
     * @param Request $request
     * @param mixed   $result
     *
     * @return ViewEvent|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getResponseEvent(Request $request, $result)
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ViewEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $result);
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
        $this->createViewResponseListener(['json' => false]);

        $viewAnnotation = new ViewAnnotation([]);
        $viewAnnotation->setOwner([$this, 'statusCodeProvider']);
        $viewAnnotation->setStatusCode($annotationCode);

        $request = new Request();
        $request->setRequestFormat('json');
        $request->attributes->set('_template', $viewAnnotation);

        $view = new View();
        $view->setStatusCode($viewCode);
        $view->setData('foo');

        $event = $this->getResponseEvent($request, $view);
        $this->listener->onKernelView($event);
        $response = $event->getResponse();

        $this->assertInstanceOf(Response::class, $response);
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
        $this->createViewResponseListener(['json' => false]);

        $viewAnnotation = new ViewAnnotation([]);
        $viewAnnotation->setOwner([$this, 'testSerializerEnableMaxDepthChecks']);
        $viewAnnotation->setSerializerEnableMaxDepthChecks($enableMaxDepthChecks);

        $request = new Request();
        $request->setRequestFormat('json');
        $request->attributes->set('_template', $viewAnnotation);

        $view = new View();

        $event = $this->getResponseEvent($request, $view);

        $this->listener->onKernelView($event);

        $context = $view->getContext();

        $this->assertEquals($enableMaxDepthChecks, $context->isMaxDepthEnabled());
    }

    public static function serializerAttributesProvider()
    {
        return [
            [['foo' => 'bar']],
            [['one', 'two', 'three']],
        ];
    }

    /**
     * @dataProvider serializerAttributesProvider
     */
    public function testSerializerAttributes(array $attributes)
    {
        $this->createViewResponseListener(['json' => false]);

        $viewAnnotation = new ViewAnnotation([]);
        $viewAnnotation->setOwner([$this, 'testSerializerEnableMaxDepthChecks']);
        $viewAnnotation->setSerializerAttributes($attributes);

        $request = new Request();
        $request->setRequestFormat('json');
        $request->attributes->set('_template', $viewAnnotation);

        $view = new View();

        $event = $this->getResponseEvent($request, $view);

        $this->listener->onKernelView($event);

        $context = $view->getContext();

        $this->assertEquals($attributes, $context->getAttributes());
    }

    public function getDataForDefaultVarsCopy()
    {
        return [
            [false],
            [true],
        ];
    }

    protected function setUp()
    {
        $this->router = $this->getMockBuilder(RouterInterface::class)->getMock();
        $this->serializer = $this->getMockBuilder(Serializer::class)->getMock();
        $this->requestStack = new RequestStack();
    }

    private function createViewResponseListener($formats = null)
    {
        $this->viewHandler = ViewHandler::create($this->router, $this->serializer, $this->requestStack, $formats);
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
