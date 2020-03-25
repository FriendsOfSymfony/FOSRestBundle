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

use FOS\RestBundle\EventListener\FormatListener;
use FOS\RestBundle\FOSRestBundle;
use FOS\RestBundle\Negotiation\FormatNegotiator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Request listener test.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 */
class FormatListenerTest extends TestCase
{
    public function testOnKernelControllerNegotiation()
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $formatNegotiator = new FormatNegotiator($requestStack);
        $formatNegotiator->add(new RequestMatcher('/'), [
            'fallback_format' => 'xml',
        ]);

        $listener = new FormatListener($formatNegotiator);

        $listener->onKernelRequest($event);

        $this->assertEquals('xml', $request->getRequestFormat());
    }

    public function testOnKernelControllerNoZone()
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        $request->attributes->set(FOSRestBundle::ZONE_ATTRIBUTE, false);

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $formatNegotiator = new FormatNegotiator($requestStack);
        $formatNegotiator->add(new RequestMatcher('/'), ['fallback_format' => 'json']);

        $listener = new FormatListener($formatNegotiator);

        $listener->onKernelRequest($event);

        $this->assertEquals($request->getRequestFormat(), 'html');
    }

    public function testOnKernelControllerNegotiationStopped()
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        $request->setRequestFormat('xml');

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $formatNegotiator = new FormatNegotiator($requestStack);
        $formatNegotiator->add(new RequestMatcher('/'), ['stop' => true]);
        $formatNegotiator->add(new RequestMatcher('/'), ['fallback_format' => 'json']);

        $listener = new FormatListener($formatNegotiator);

        $listener->onKernelRequest($event);

        $this->assertEquals($request->getRequestFormat(), 'xml');
    }

    public function testOnKernelControllerException()
    {
        $this->expectException(HttpException::class);

        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->once())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $requestStack = new RequestStack();
        $requestStack->push($request);

        $listener = new FormatListener(new FormatNegotiator($requestStack));

        $listener->onKernelRequest($event);
    }

    /**
     * Test FormatListener won't overwrite request format when it was already specified.
     *
     * @dataProvider useSpecifiedFormatDataProvider
     */
    public function testUseSpecifiedFormat($format, $result)
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        if ($format) {
            $request->setRequestFormat($format);
        }

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $formatNegotiator = new FormatNegotiator($requestStack);
        $formatNegotiator->add(new RequestMatcher('/'), [
            'fallback_format' => 'xml',
        ]);

        $listener = new FormatListener($formatNegotiator);

        $listener->onKernelRequest($event);

        $this->assertEquals($request->getRequestFormat(), $result);
    }

    public function useSpecifiedFormatDataProvider()
    {
        return [
            [null, 'xml'],
            ['json', 'json'],
        ];
    }

    /**
     * Generates a request like a symfony fragment listener does.
     * Set request type to master.
     */
    public function testSfFragmentFormat()
    {
        $event = $this->getMockBuilder(RequestEvent::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request = new Request();
        $attributes = ['_locale' => 'en', '_format' => 'json', '_controller' => 'FooBundle:Index:featured'];
        $request->attributes->add($attributes);
        $request->attributes->set('_route_params', array_replace($request->attributes->get('_route_params', []), $attributes));

        $event->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($request));

        $event->expects($this->any())
            ->method('getRequestType')
            ->will($this->returnValue(HttpKernelInterface::MASTER_REQUEST));

        $requestStack = new RequestStack();
        $requestStack->push($request);
        $formatNegotiator = new FormatNegotiator($requestStack);
        $formatNegotiator->add(new RequestMatcher('/'), [
            'fallback_format' => 'json',
        ]);

        $listener = new FormatListener($formatNegotiator);

        $listener->onKernelRequest($event);

        $this->assertEquals($request->getRequestFormat(), 'json');
    }
}
