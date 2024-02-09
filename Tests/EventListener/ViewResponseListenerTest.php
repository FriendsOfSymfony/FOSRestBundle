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

use Doctrine\Common\Annotations\Reader;
use FOS\RestBundle\Controller\Annotations\View as ViewAnnotation;
use FOS\RestBundle\EventListener\ViewResponseListener;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Serializer\Serializer;
use FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Controller\Version2Controller;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\ViewHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var (MockObject&Reader)|null
     */
    public $annotationReader;

    /**
     * @var ViewHandlerInterface
     */
    public $viewHandler;

    private $router;
    private $serializer;
    private $requestStack;

    protected function getControllerEvent(Request $request, callable $controller): ControllerEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ControllerEvent($kernel, $controller, $request, HttpKernelInterface::MASTER_REQUEST);
    }

    public function testExtractsViewConfigurationFromAnnotationOnMethod()
    {
        if (null === $this->annotationReader || 80000 <= \PHP_VERSION_ID) {
            $this->markTestSkipped('Test only applies when doctrine/annotations is installed and running on PHP 7');
        }

        $this->createViewResponseListener();

        $this->annotationReader->expects($this->once())
            ->method('getClassAnnotations')
            ->willReturn([]);

        $this->annotationReader->expects($this->once())
            ->method('getMethodAnnotations')
            ->willReturn([new ViewAnnotation()]);

        $controller = new Version2Controller();

        $request = new Request();
        $event = $this->getControllerEvent($request, [$controller, 'versionAction']);

        $this->listener->onKernelController($event);

        $config = $request->attributes->get(FOSRestBundle::VIEW_ATTRIBUTE);

        $this->assertNotNull($config);
        $this->assertInstanceOf(ViewAnnotation::class, $config);
    }

    /**
     * @requires PHP 8.0
     */
    public function testExtractsViewConfigurationFromAttributeOnMethod()
    {
        $this->createViewResponseListener();

        $controller = new Version2Controller();

        $request = new Request();
        $event = $this->getControllerEvent($request, [$controller, 'versionAction']);

        $this->listener->onKernelController($event);

        $config = $request->attributes->get(FOSRestBundle::VIEW_ATTRIBUTE);

        $this->assertNotNull($config);
        $this->assertInstanceOf(ViewAnnotation::class, $config);
    }

    /**
     * @param mixed $result
     */
    protected function getViewEvent(Request $request, $result): ViewEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);

        return new ViewEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $result);
    }

    public function testOnKernelViewWhenControllerResultIsNotViewObject()
    {
        $this->createViewResponseListener();

        $request = new Request();
        $event = $this->getViewEvent($request, []);

        $this->listener->onKernelView($event);

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
        $request->attributes->set(FOSRestBundle::VIEW_ATTRIBUTE, $viewAnnotation);

        $view = new View();
        $view->setStatusCode($viewCode);
        $view->setData('foo');

        $event = $this->getViewEvent($request, $view);
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
        $request->attributes->set(FOSRestBundle::VIEW_ATTRIBUTE, $viewAnnotation);

        $view = new View();

        $event = $this->getViewEvent($request, $view);

        $this->listener->onKernelView($event);

        $context = $view->getContext();

        $this->assertEquals($enableMaxDepthChecks, $context->isMaxDepthEnabled());
    }

    public function getDataForDefaultVarsCopy()
    {
        return [
            [false],
            [true],
        ];
    }

    protected function setUp(): void
    {
        $this->annotationReader = interface_exists(Reader::class) && 80000 > \PHP_VERSION_ID ? $this->getMockBuilder(Reader::class)->getMock() : null;
        $this->router = $this->getMockBuilder(RouterInterface::class)->getMock();
        $this->serializer = $this->getMockBuilder(Serializer::class)->getMock();
        $this->requestStack = new RequestStack();
    }

    private function createViewResponseListener($formats = null)
    {
        $this->viewHandler = ViewHandler::create($this->router, $this->serializer, $this->requestStack, $formats);
        $this->listener = new ViewResponseListener($this->viewHandler, false, $this->annotationReader);
    }
}
