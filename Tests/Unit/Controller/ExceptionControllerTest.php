<?php

declare(strict_types=1);

namespace FOS\RestBundle\Tests\Unit\Controller;

use FOS\RestBundle\Controller\ExceptionController;
use FOS\RestBundle\Util\ExceptionValueMap;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandlerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ExceptionControllerTest extends TestCase
{
    /** @var MockObject|ViewHandlerInterface */
    private $viewHandler;

    /** @var MockObject|ExceptionValueMap */
    private $exceptionCodes;

    /** @var ExceptionController */
    private $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->viewHandler = $this->createMock(ViewHandlerInterface::class);
        $this->exceptionCodes = $this->createMock(ExceptionValueMap::class);

        $this->service = new ExceptionController($this->viewHandler, $this->exceptionCodes, true);
    }

    public function testShowActionWithError(): void
    {
        /** @var MockObject|Request $request */
        $request = $this->createMock(Request::class);
        $request->headers = $this->createMock(HeaderBag::class);

        /** @var MockObject|Response $response */
        $response = $this->createMock(Response::class);

        /** @var \Error $exception */
        $exception = new \Error();

        $request->headers->expects($this->once())
            ->method('get')
            ->with('X-Php-Ob-Level', -1)
            ->willReturn(2);

        $this->viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(View::class))
            ->willReturn($response);

        $actual = $this->service->showAction($request, $exception);

        TestCase::assertEquals($response, $actual);
    }

    public function testShowActionWithException(): void
    {
        /** @var MockObject|Request $request */
        $request = $this->createMock(Request::class);
        $request->headers = $this->createMock(HeaderBag::class);

        /** @var MockObject|Response $response */
        $response = $this->createMock(Response::class);

        /** @var \Exception $exception */
        $exception = new \Exception();

        $request->headers->expects($this->once())
            ->method('get')
            ->with('X-Php-Ob-Level', -1)
            ->willReturn(2);

        $this->viewHandler->expects($this->once())
            ->method('handle')
            ->with($this->isInstanceOf(View::class))
            ->willReturn($response);

        $actual = $this->service->showAction($request, $exception);

        TestCase::assertEquals($response, $actual);
    }
}
