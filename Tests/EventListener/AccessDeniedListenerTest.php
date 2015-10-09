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
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * AccessDeniedListenerTest.
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */
class AccessDeniedListenerTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (!class_exists('Symfony\Component\HttpFoundation\Request')) {
            $this->markTestSkipped('The "HttpFoundation" component is not available');
        }

        if (!class_exists('Symfony\Component\Security\Core\Exception\AccessDeniedException')) {
            $this->markTestSkipped('The "Security" component is not available');
        }
    }

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
    private function doTestAccessDeniedExceptionIsConvertedToAnAccessDeniedHttpExceptionForRequest(Request $request, array $formats)
    {
        $exception = new AccessDeniedException();
        $event = new GetResponseForExceptionEvent(new TestKernel(), $request, 'foo', $exception);
        $listener = new AccessDeniedListener($formats, null, 'foo');
        // store the current error_log, and disable it temporarily
        $errorLog = ini_set('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');
        $listener->onKernelException($event);
        // restore the old error_log
        ini_set('error_log', $errorLog);
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException', $event->getException());
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
        $event = new GetResponseForExceptionEvent(new TestKernel(), $request, 'foo', $exception);

        $listener = new AccessDeniedListener($formats, null, 'foo');
        $listener->onKernelException($event);
        $this->assertSame($exception, $event->getException());
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
        $event = new GetResponseForExceptionEvent(new TestKernel(), $request, 'foo', $exception);
        $listener = new AccessDeniedListener($formats, null, 'foo');
        // store the current error_log, and disable it temporarily
        $errorLog = ini_set('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');
        $listener->onKernelException($event);
        // restore the old error_log
        ini_set('error_log', $errorLog);
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\HttpException', $event->getException());
        $this->assertEquals(401, $event->getException()->getStatusCode());
        $this->assertEquals('You are not authenticated', $event->getException()->getMessage());
        $this->assertArrayNotHasKey('WWW-Authenticate', $event->getException()->getHeaders());
    }

    /**
     * @param Request $request
     * @param array   $formats
     */
    private function doTestUnauthorizedHttpExceptionHasCorrectChallenge(Request $request, array $formats)
    {
        $exception = new AuthenticationException();
        $event = new GetResponseForExceptionEvent(new TestKernel(), $request, 'foo', $exception);
        $listener = new AccessDeniedListener($formats, 'Basic realm="Restricted Area"', 'foo');
        // store the current error_log, and disable it temporarily
        $errorLog = ini_set('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');
        $listener->onKernelException($event);
        // restore the old error_log
        ini_set('error_log', $errorLog);
        $this->assertInstanceOf('Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException', $event->getException());
        $this->assertEquals(401, $event->getException()->getStatusCode());
        $this->assertEquals('You are not authenticated', $event->getException()->getMessage());
        $headers = $event->getException()->getHeaders();
        $this->assertEquals('Basic realm="Restricted Area"', $headers['WWW-Authenticate']);
    }

    public static function getFormatsDataProvider()
    {
        return array(
            array(array('json' => true), 'json'),
        );
    }

    public static function getContentTypesDataProvider()
    {
        return array(
            array(array('json' => true), 'application/json'),
        );
    }
}

class TestKernel implements HttpKernelInterface
{
    public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
    {
        return new Response('foo');
    }
}
