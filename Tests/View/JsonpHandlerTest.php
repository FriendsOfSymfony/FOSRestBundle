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

use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use FOS\RestBundle\View\JsonpHandler;
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
        $data = array('foo' => 'bar');

        $viewHandler = new ViewHandler(array('jsonp' => false));
        $jsonpHandler = new JsonpHandler(key($query));
        $viewHandler->registerHandler('jsonp', array($jsonpHandler, 'createResponse'));

        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get', 'getParameter'));
        $serializer = $this->getMock('stdClass', array('serialize', 'setVersion'));
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue(var_export($data, true)));

        $container
            ->expects($this->once())
            ->method('get')
            ->with('fos_rest.serializer')
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
        return array(
            'jQuery callback syntax' => array(array('callback' => 'jQuery171065827149929257_1343950463342')),
            'YUI callback syntax' => array(array('callback' => 'YUI.Env.JSONP._12345')),
            'jQuery custom syntax' => array(array('custom' => 'jQuery171065827149929257_1343950463342')),
            'YUI custom syntax' => array(array('custom' => 'YUI.Env.JSONP._12345')),
        );
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @dataProvider getCallbackFailureDataProvider
     */
    public function testGetCallbackFailure(Request $request)
    {
        $data = array('foo' => 'bar');

        $viewHandler = new ViewHandler(array('jsonp' => false));
        $jsonpHandler = new JsonpHandler('callback');
        $viewHandler->registerHandler('jsonp', array($jsonpHandler, 'createResponse'));

        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get', 'getParameter'));
        $serializer = $this->getMock('stdClass', array('serialize', 'setVersion'));
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue(var_export($data, true)));

        $container
            ->expects($this->once())
            ->method('get')
            ->with('fos_rest.serializer')
            ->will($this->returnValue($serializer));

        $container
            ->expects($this->any())
            ->method('getParameter')
            ->will($this->onConsecutiveCalls('version', '1.0'));

        $viewHandler->setContainer($container);

        $data = array('foo' => 'bar');

        $view = new View($data);
        $view->setFormat('jsonp');
        $viewHandler->handle($view, $request);
    }

    public function getCallbackFailureDataProvider()
    {
        return array(
            'no callback' => array(new Request()),
            'incorrect callback param name' => array(new Request(array('foo' => 'bar'))),
        );
    }
}
