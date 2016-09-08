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
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Jsonp handler test.
 *
 * @author Victor Berchet <victor@suumit.com>
 * @author Lukas K. Smith <smith@pooteeweet.org>
 */
class JsonpHandlerTest extends \PHPUnit_Framework_TestCase
{
    private $router;
    private $serializer;
    private $templating;
    private $requestStack;

    protected function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
        $this->serializer = $this->getMockBuilder('FOS\RestBundle\Serializer\Serializer')->getMock();
        $this->templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface')->getMock();
        $this->requestStack = new RequestStack();
    }

    /**
     * @dataProvider handleDataProvider
     */
    public function testHandle($query)
    {
        $data = ['foo' => 'bar'];

        $viewHandler = new ViewHandler($this->router, $this->serializer, $this->templating, $this->requestStack, ['jsonp' => false]);
        $jsonpHandler = new JsonpHandler(key($query));
        $viewHandler->registerHandler('jsonp', [$jsonpHandler, 'createResponse']);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue(var_export($data, true)));

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

        $viewHandler = new ViewHandler($this->router, $this->serializer, $this->templating, $this->requestStack, ['jsonp' => false]);
        $jsonpHandler = new JsonpHandler('callback');
        $viewHandler->registerHandler('jsonp', [$jsonpHandler, 'createResponse']);

        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue(var_export($data, true)));

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
