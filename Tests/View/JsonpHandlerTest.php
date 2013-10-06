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
 * Jsonp handler test
 *
 * @author Victor Berchet <victor@suumit.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class JsonpHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle($query, $callbackFilter = '/(^[a-z0-9_]+$)|(^YUI\.Env\.JSONP\._[0-9]+$)/i')
    {
        $data = array('foo' => 'bar');

        $viewHandler = new ViewHandler(array('jsonp' => false));
        $jsonpHandler = new JsonpHandler(key($query), $callbackFilter);
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

        $this->assertEquals(reset($query).'('.var_export($data, true).')', $response->getContent());
    }

    public static function handleDataProvider()
    {
        return array(
            'jQuery callback syntax' => array(array('callback' => 'jQuery171065827149929257_1343950463342')),
            'YUI callback syntax' => array(array('callback' => 'YUI.Env.JSONP._12345')),
            'custom callback param' => array(array('custom' => '1234')),
            'custom callback filter' => array(array('custom' => '1234.asdas.122'), false),
        );
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     * @dataProvider getCallbackFailureDataProvider
     */
    public function testGetCallbackFailure(Request $request, $callbackFilter = '/(^[a-z0-9_]+$)|(^YUI\.Env\.JSONP\._[0-9]+$)/i')
    {
        $data = array('foo' => 'bar');

        $viewHandler = new ViewHandler(array('jsonp' => false));
        $jsonpHandler = new JsonpHandler('callback', $callbackFilter);
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
            'no callback'   => array(new Request()),
            'incorrect callback param name'  => array(new Request(array('foo' => 'bar'))),
            'incorrect callback param value' => array(new Request(array('callback' => 'ding.dong'))),
            'incorrect callback param name and value' => array(new Request(array('foo' => 'bar'))),
            'incorrect callback param value with a custom filter' => array(new Request(array('foo' => 'bar')), '/[0-9]+/'),
        );
    }
}
