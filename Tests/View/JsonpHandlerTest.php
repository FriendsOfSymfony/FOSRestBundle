<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\View;

use FOS\RestBundle\View\JsonpHandler;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use Symfony\Component\HttpFoundation\Request;

/**
 * Jsonp handler test.
 *
 * @author Victor Berchet <victor@suumit.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class JsonpHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle($query)
    {
        $data = ['foo' => 'bar'];

        $viewHandler = new ViewHandler(['jsonp' => false]);
        $jsonpHandler = new JsonpHandler(key($query));
        $viewHandler->registerHandler('jsonp', [$jsonpHandler, 'createResponse']);
        $viewHandler->setSerializationContextAdapter($this->getMock('FOS\RestBundle\Context\Adapter\SerializationContextAdapterInterface'));

        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', ['get', 'getParameter']);
        $serializer = $this->getMock('stdClass', ['serialize', 'setVersion']);
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue(var_export($data, true)));

        $container
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo('fos_rest.serializer'))
            ->will($this->returnValue($serializer));

        $container
            ->expects($this->any())
            ->method('getParameter')
            ->will($this->onConsecutiveCalls('version', '1.0'));

        $viewHandler->setContainer($container);

        $view = new View($data);
        $view->setFormat('jsonp');
        $request = new Request($query);

        $response = $viewHandler->handle($view, $request);

        $this->assertEquals('/**/'.reset($query).'('.var_export($data, true).')', $response->getContent());
    }

    public static function handleDataProvider()
    {
        return [
            'jQuery callback syntax' => [['callback' => 'jQuery171065827149929257_1343950463342']],
            'YUI callback syntax' => [['callback' => 'YUI.Env.JSONP._12345']],
            'jQuery custom syntax' => [['custom' => 'jQuery171065827149929257_1343950463342']],
            'YUI custom syntax' => [['custom' => 'YUI.Env.JSONP._12345']],
        ];
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @dataProvider getCallbackFailureDataProvider
     */
    public function testGetCallbackFailure(Request $request)
    {
        $data = ['foo' => 'bar'];

        $viewHandler = new ViewHandler(['jsonp' => false]);
        $jsonpHandler = new JsonpHandler('callback');
        $viewHandler->registerHandler('jsonp', [$jsonpHandler, 'createResponse']);
        $viewHandler->setSerializationContextAdapter($this->getMock('FOS\RestBundle\Context\Adapter\SerializationContextAdapterInterface'));

        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', ['get', 'getParameter']);
        $serializer = $this->getMock('stdClass', ['serialize', 'setVersion']);
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue(var_export($data, true)));

        $container
            ->expects($this->any())
            ->method('get')
            ->with($this->equalTo('fos_rest.serializer'))
            ->will($this->returnValue($serializer));

        $container
            ->expects($this->any())
            ->method('getParameter')
            ->will($this->onConsecutiveCalls('version', '1.0'));

        $viewHandler->setContainer($container);

        $data = ['foo' => 'bar'];

        $view = new View($data);
        $view->setFormat('jsonp');
        $viewHandler->handle($view, $request);
    }

    public function getCallbackFailureDataProvider()
    {
        return [
            'no callback' => [new Request()],
            'incorrect callback param name' => [new Request(['foo' => 'bar'])],
        ];
    }
}
