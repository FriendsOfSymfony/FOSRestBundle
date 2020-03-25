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

use FOS\RestBundle\EventListener\AccessDeniedListener;
use FOS\RestBundle\FOSRestBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AccessDeniedListenerTest.
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */
class AccessDeniedListenerTest extends TestCase
{
    /**
     * @dataProvider getFormatsDataProvider
     *
     * @param array  $formats
     * @param string $format
     */
    public function testAccessDeniedExceptionIsConvertedToAnAccessDeniedHttpExceptionForFormat(array $formats, $format)
    {
        $request = new Request();
        $request->setRequestFormat($format);

        $this->doTestAccessDeniedExceptionIsConvertedToAnAccessDeniedHttpExceptionForRequest($request, $formats);
    }

    /**
     * @dataProvider getFormatsDataProvider
     *
     * @param array  $formats
     * @param string $format
     */
    public function testAccessDeniedExceptionIsConvertedToAnAccessDeniedHttpExceptionForFormatNoZone(array $formats, $format)
    {
        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);
        $request->setRequestFormat($format);

        $this->doTestAccessDeniedExceptionIsConvertedToAnAccessDeniedHttpExceptionForRequest($request, $formats, 'Symfony\Component\Security\Core\Exception\AccessDeniedException');
    }

    /**
     * @dataProvider getContentTypesDataProvider
     *
     * @param array  $formats
     * @param string $contentType
     */
    public function testAccessDeniedExceptionIsConvertedToAnAccessDeniedHttpExceptionForContentType(array $formats, $contentType)
    {
        $request = new Request();
        $request->headers->set('Content-Type', $contentType);

        $this->doTestAccessDeniedExceptionIsConvertedToAnAccessDeniedHttpExceptionForRequest($request, $formats);
    }

    /**
     * @param Request $request
     * @param array   $formats
     */
    private function doTestAccessDeniedExceptionIsConvertedToAnAccessDeniedHttpExceptionForRequest(Request $request, array $formats, $exceptionClass = 'Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException')
    {
        $exception = new AccessDeniedException();
        $eventClass = class_exists(ExceptionEvent::class) ? ExceptionEvent::class : GetResponseForExceptionEvent::class;
        $event = new $eventClass(new TestKernel(), $request, Kernel::MASTER_REQUEST, $exception);
        $listener = new AccessDeniedListener($formats, null, 'foo');
        // store the current error_log, and disable it temporarily
        $errorLog = ini_set('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');
        $listener->onKernelException($event);
        // restore the old error_log
        ini_set('error_log', $errorLog);
        if (method_exists($event, 'getThrowable')) {
            $exception = $event->getThrowable();
        } else {
            $exception = $event->getException();
        }
        $this->assertInstanceOf($exceptionClass, $exception);
    }

    /**
     * @dataProvider getFormatsDataProvider
     *
     * @param array $formats
     */
    public function testCommonExceptionsAreBypassed($formats)
    {
        $request = new Request();
        $request->setRequestFormat(key($formats));
        $exception = new \Exception('foo');
        $eventClass = class_exists(ExceptionEvent::class) ? ExceptionEvent::class : GetResponseForExceptionEvent::class;
        $event = new $eventClass(new TestKernel(), $request, Kernel::MASTER_REQUEST, $exception);

        $listener = new AccessDeniedListener($formats, null, 'foo');
        $listener->onKernelException($event);
        if (method_exists($event, 'getThrowable')) {
            $thrownException = $event->getThrowable();
        } else {
            $thrownException = $event->getException();
        }
        $this->assertSame($exception, $thrownException);
    }

    /**
     * @dataProvider getFormatsDataProvider
     *
     * @param array  $formats
     * @param string $format
     */
    public function testAuthenticationExceptionIsConvertedToAnAccessDeniedHttpExceptionForFormat(array $formats, $format)
    {
        $request = new Request();
        $request->setRequestFormat($format);

        $this->doTestAuthenticationExceptionIsConvertedToAnHttpExceptionForRequest($request, $formats);
    }

    /**
     * @dataProvider getContentTypesDataProvider
     *
     * @param array  $formats
     * @param string $contentType
     */
    public function testAuthenticationExceptionIsConvertedToAnAccessDeniedHttpExceptionForContentType(array $formats, $contentType)
    {
        $request = new Request();
        $request->headers->set('Content-Type', $contentType);

        $this->doTestAuthenticationExceptionIsConvertedToAnHttpExceptionForRequest($request, $formats);
    }

    /**
     * @param Request $request
     * @param array   $formats
     */
    private function doTestAuthenticationExceptionIsConvertedToAnHttpExceptionForRequest(Request $request, array $formats)
    {
        $exception = new AuthenticationException();
        $eventClass = class_exists(ExceptionEvent::class) ? ExceptionEvent::class : GetResponseForExceptionEvent::class;
        $event = new $eventClass(new TestKernel(), $request, Kernel::MASTER_REQUEST, $exception);
        $listener = new AccessDeniedListener($formats, null, 'foo');
        // store the current error_log, and disable it temporarily
        $errorLog = ini_set('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');
        $listener->onKernelException($event);
        // restore the old error_log
        ini_set('error_log', $errorLog);
        if (method_exists($event, 'getThrowable')) {
            $exception = $event->getThrowable();
        } else {
            $exception = $event->getException();
        }
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\HttpException', $exception);
        $this->assertEquals(401, $exception->getStatusCode());
        $this->assertEquals('You are not authenticated', $exception->getMessage());
        $this->assertArrayNotHasKey('WWW-Authenticate', $exception->getHeaders());
    }

    /**
     * @param Request $request
     * @param array   $formats
     */
    private function doTestUnauthorizedHttpExceptionHasCorrectChallenge(Request $request, array $formats)
    {
        $exception = new AuthenticationException();
        $eventClass = class_exists(ExceptionEvent::class) ? ExceptionEvent::class : GetResponseForExceptionEvent::class;
        $event = new $eventClass(new TestKernel(), $request, 'foo', $exception);
        $listener = new AccessDeniedListener($formats, 'Basic realm="Restricted Area"', 'foo');
        // store the current error_log, and disable it temporarily
        $errorLog = ini_set('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');
        $listener->onKernelException($event);
        // restore the old error_log
        ini_set('error_log', $errorLog);
        if (method_exists($event, 'getThrowable')) {
            $exception = $event->getThrowable();
        } else {
            $exception = $event->getException();
        }
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException', $exception);
        $this->assertEquals(401, $exception->getStatusCode());
        $this->assertEquals('You are not authenticated', $exception->getMessage());
        $headers = $exception->getHeaders();
        $this->assertEquals('Basic realm="Restricted Area"', $headers['WWW-Authenticate']);
    }

    public static function getFormatsDataProvider()
    {
        return [
            [['json' => true], 'json'],
        ];
    }

    public static function getContentTypesDataProvider()
    {
        return [
            [['json' => true], 'application/json'],
        ];
    }
}

class TestKernel implements HttpKernelInterface
{
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        return new Response('foo');
    }
}
