<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EventListener;

use FOS\RestBundle\EventListener\ResponseStatusCodeListener;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Util\ExceptionValueMap;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ResponseStatusCodeListenerTest extends TestCase
{
    private $eventListener;

    protected function setUp(): void
    {
        $this->eventListener = new ResponseStatusCodeListener(new ExceptionValueMap([
            \DomainException::class => 400,
            NotFoundHttpException::class => 404,
            \ParseError::class => 500,
        ]));
    }

    public function testResponseStatusCodeIsNotSetWhenRequestNotInRestZone()
    {
        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);

        if (class_exists(ExceptionEvent::class)) {
            $exceptionEvent = new ExceptionEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, new \DomainException());
        } else {
            $exceptionEvent = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, new \DomainException());
        }

        $this->eventListener->getResponseStatusCodeFromThrowable($exceptionEvent);

        $response = new Response();

        if (class_exists(ResponseEvent::class)) {
            $responseEvent = new ResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);
        } else {
            $responseEvent = new FilterResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);
        }

        $this->eventListener->setResponseStatusCode($responseEvent);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testResponseStatusCodeIsNotSetWhenExceptionIsNotMapped()
    {
        $request = new Request();

        if (class_exists(ExceptionEvent::class)) {
            $exceptionEvent = new ExceptionEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, new \LogicException());
        } else {
            $exceptionEvent = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, new \LogicException());
        }

        $this->eventListener->getResponseStatusCodeFromThrowable($exceptionEvent);

        $response = new Response();

        if (class_exists(ResponseEvent::class)) {
            $responseEvent = new ResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);
        } else {
            $responseEvent = new FilterResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);
        }

        $this->eventListener->setResponseStatusCode($responseEvent);

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testResponseStatusCodeIsSetWhenExceptionTypeIsConfigured()
    {
        $request = new Request();
        $exception = new \DomainException();

        if (class_exists(ExceptionEvent::class)) {
            $exceptionEvent = new ExceptionEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        } else {
            $exceptionEvent = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $exception);
        }

        $this->eventListener->getResponseStatusCodeFromThrowable($exceptionEvent);

        $response = new Response();

        if (class_exists(ResponseEvent::class)) {
            $responseEvent = new ResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);
        } else {
            $responseEvent = new FilterResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);
        }

        $this->eventListener->setResponseStatusCode($responseEvent);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testResponseStatusCodeIsSetWhenErrorTypeIsConfigured()
    {
        if (!method_exists(ExceptionEvent::class, 'getThrowable')) {
            $this->markTestSkipped();
        }

        $request = new Request();

        $this->eventListener->getResponseStatusCodeFromThrowable(new ExceptionEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, new \ParseError()));

        $response = new Response();

        if (class_exists(ResponseEvent::class)) {
            $responseEvent = new ResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);
        } else {
            $responseEvent = new FilterResponseEvent($this->createMock(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST, $response);
        }

        $this->eventListener->setResponseStatusCode($responseEvent);

        $this->assertSame(500, $response->getStatusCode());
    }

    public function testResponseStatusCodeIsNotOverriddenInSubRequests()
    {
        $masterRequest = new Request();
        $exception = new NotFoundHttpException();

        if (class_exists(ExceptionEvent::class)) {
            $masterRequestExceptionEvent = new ExceptionEvent($this->createMock(HttpKernelInterface::class), $masterRequest, HttpKernelInterface::MAIN_REQUEST, $exception);
        } else {
            $masterRequestExceptionEvent = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), $masterRequest, HttpKernelInterface::MAIN_REQUEST, $exception);
        }

        $this->eventListener->getResponseStatusCodeFromThrowable($masterRequestExceptionEvent);

        $subRequest = new Request();
        $exception = new \DomainException();

        if (class_exists(ExceptionEvent::class)) {
            $subRequestExceptionEvent = new ExceptionEvent($this->createMock(HttpKernelInterface::class), $subRequest, HttpKernelInterface::SUB_REQUEST, $exception);
        } else {
            $subRequestExceptionEvent = new GetResponseForExceptionEvent($this->createMock(HttpKernelInterface::class), $subRequest, HttpKernelInterface::SUB_REQUEST, $exception);
        }

        $this->eventListener->getResponseStatusCodeFromThrowable($subRequestExceptionEvent);

        $subRequestResponse = new Response();

        if (class_exists(ResponseEvent::class)) {
            $subRequestResponseEvent = new ResponseEvent($this->createMock(HttpKernelInterface::class), $subRequest, HttpKernelInterface::SUB_REQUEST, $subRequestResponse);
        } else {
            $subRequestResponseEvent = new FilterResponseEvent($this->createMock(HttpKernelInterface::class), $subRequest, HttpKernelInterface::SUB_REQUEST, $subRequestResponse);
        }

        $this->eventListener->setResponseStatusCode($subRequestResponseEvent);

        $masterRequestResponse = new Response();

        if (class_exists(ResponseEvent::class)) {
            $masterRequestResponseEvent = new ResponseEvent($this->createMock(HttpKernelInterface::class), $masterRequest, HttpKernelInterface::MAIN_REQUEST, $masterRequestResponse);
        } else {
            $masterRequestResponseEvent = new FilterResponseEvent($this->createMock(HttpKernelInterface::class), $masterRequest, HttpKernelInterface::MAIN_REQUEST, $masterRequestResponse);
        }

        $this->eventListener->setResponseStatusCode($masterRequestResponseEvent);

        $this->assertSame(404, $masterRequestResponseEvent->getResponse()->getStatusCode());
    }
}
