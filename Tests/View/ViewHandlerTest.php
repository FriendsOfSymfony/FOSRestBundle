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

use FOS\RestBundle\View\View,
    FOS\RestBundle\View\RedirectView,
    FOS\RestBundle\View\RouteRedirectView,
    FOS\RestBundle\View\ViewHandler,
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference,
    FOS\RestBundle\Response\Codes,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

/**
 * View test
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class ViewHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider supportsFormatDataProvider
     */
    public function testSupportsFormat($expected, $formatName, $customFormatName)
    {
        $viewHandler = new ViewHandler();
        $viewHandler->registerHandler($customFormatName, function(){});

        $this->assertEquals($expected, $viewHandler->supports('html'));
    }

    public static function supportsFormatDataProvider()
    {
        return array(
            'not supported'   => array(false, array('json' => true), 'xml'),
            'html default'   => array(true, array('html' => 'templating'), 'xml'),
            'html custom'   => array(true, array('json' => true), 'html'),
            'html both'   => array(true, array('html' => 'templating'), 'html'),
        );
    }

    public function testRegsiterHandle()
    {
        $viewHandler = new ViewHandler();
        $viewHandler->registerHandler('html', ($callback = function(){}));
        $this->assertAttributeEquals(array('html' => $callback), 'customHandlers', $viewHandler);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterHandleExpectsException()
    {
        $viewHandler = new ViewHandler();

        $viewHandler->registerHandler('json', new \stdClass());
    }

    public function testSetLocation()
    {
        $url = 'users';
        $code = 500;

        $view = RedirectView::create($url, $code);
        $this->assertAttributeEquals($url, 'location', $view);
        $this->assertAttributeEquals(null, 'route', $view);
        $this->assertAttributeEquals($code, 'code', $view);
    }

    public function testSetRoute()
    {
        $routeName = 'users';
        $code = 500;

        $view = RouteRedirectView::create($routeName, array(), $code);
        $this->assertAttributeEquals($routeName, 'route', $view);
        $this->assertAttributeEquals(null, 'location', $view);
        $this->assertAttributeEquals(Codes::HTTP_CREATED, 'code', $view);

        $view->setLocation($routeName);
        $this->assertAttributeEquals($routeName, 'location', $view);
        $this->assertAttributeEquals(null, 'route', $view);
    }

    /**
     * @dataProvider getStatusCodeFromViewDataProvider
     */
    public function testGetStatusCodeFromView($expected, $key = false, $isBound = false, $isValid = false, $isBoundCalled = 0, $isValidCalled = 0)
    {
        $reflectionMethod = new \ReflectionMethod('\FOS\RestBundle\View\ViewHandler', 'getStatusCodeFromView');
        $reflectionMethod->setAccessible(true);

        $form = $this->getMock('\Symfony\Component\Form\Form', array('isBound', 'isValid'), array(), '', false);
        $form
            ->expects($this->exactly($isBoundCalled))
            ->method('isBound')
            ->will($this->returnValue($isBound));
        $form
            ->expects($this->exactly($isValidCalled))
            ->method('isValid')
            ->will($this->returnValue($isValid));

        $data = array('form' => $form);
        $view =  new View($data);

        $viewHandler = new ViewHandler();
        $this->assertEquals($expected, $reflectionMethod->invoke($viewHandler, $view));
    }

    public static function getStatusCodeFromViewDataProvider()
    {
        return array(
            'no form key' => array(Codes::HTTP_OK),
            'form key form not bound' => array(Codes::HTTP_OK, 'foo', false, true, 1),
            'form key form is bound and invalid' => array(403, 'foo', true, false, 1, 1),
            'form key form bound and valid' => array(Codes::HTTP_OK, 'foo', true, true, 1, 1),
            'form key null form bound and valid' => array(Codes::HTTP_OK, null, true, true, 1, 1),
        );
    }

    /**
     * @dataProvider createResponseWithLocationDataProvider
     */
    public function testCreateResponseWithLocation($expected, $origStatusCode, $format, $isRedirectCalls = 0, $isRedirect = false, $setContentCalls = 0)
    {
        $response = $this->getMock('\Symfony\Component\HttpFoundation\Response', array('isRedirect', 'setContent'));
        $response
            ->expects($this->exactly($isRedirectCalls))
            ->method('isRedirect')
            ->will($this->returnValue($isRedirect));
        $response
            ->expects($this->exactly($setContentCalls))
            ->method('setContent');
        $response->setStatusCode($origStatusCode);

        $viewHandler = new ViewHandlerProxy(null, Codes::HTTP_BAD_REQUEST, array('json' => 403));
        $view = new View();
        $view->setLocation('foo');
        $returnedResponse = $viewHandler->createResponse(new \Symfony\Component\HttpFoundation\Request(), $view, $format);
        $this->assertEquals($expected, $returnedResponse->getStatusCode());
        $this->assertEquals('foo', $response->headers->get('location'));
    }

    public static function createResponseWithLocationDataProvider()
    {
        return array(
            'empty forceredirects' => array(200, 200, 'xml'),
            'forceredirects response is redirect' => array(200, 200, 'json', 1, true),
            'forceredirects response not redirect' => array(403, 200, 'json', 1),
            'html and redirect' => array(301, 301, 'html', 1, true, 1),
        );
    }

    /**
     * @dataProvider createResponseWithoutLocationDataProvider
     */
    public function testCreateResonseWithoutLocation($format, $expected, $createViewCalls = 0, $formIsValid = false, $form = false, $getChildrenCalls = 0, $getErrorsCalls = 0)
    {
        $child = $this->getMock('\stdClass', array('getErrors'));
        $child
            ->expects($this->exactly($getErrorsCalls))
            ->method('getErrors')
            ->will($this->returnValue('error'));

        $viewHandler =  new ViewHandlerProxy(array('html' => 'templating', 'json' => true));


        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container', array('get'));
        if ('html' === $format) {
            $templating = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Templating\PhpEngine')
                ->setMethods(array('render'))
                ->disableOriginalConstructor()
                ->getMock();
            $templating
                ->expects($this->once())
                ->method('render')
                ->will($this->returnValue(var_export($expected, true)));

            $container
                ->expects($this->once())
                ->method('get')
                ->with('templating')
                ->will($this->returnValue($templating));
        } else {
            $serializer = $this->getMock('\stdClass', array('serialize'));
            $serializer
                ->expects($this->once())
                ->method('serialize')
                ->will($this->returnValue(var_export($expected, true)));

            $container
                ->expects($this->once())
                ->method('get')
                ->with('serializer')
                ->will($this->returnValue($serializer));
        }

        $viewHandler->setContainer($container);

        $data = array('foo' => 'bar');
        if ($form) {
            $form = $this->getMock('\Symfony\Component\Form\Form', array('createView', 'isValid', 'getChildren'), array(), '', false);
            $form
                ->expects($this->exactly($createViewCalls))
                ->method('createView')
                ->will($this->returnValue(array('bla' => 'toto')));
            $form
                ->expects($this->any())
                ->method('isValid')
                ->will($this->returnValue($formIsValid));
            $form
                ->expects($this->exactly($getChildrenCalls))
                ->method('getChildren')
                ->will($this->returnValue(array($child, $child)));
            $data['form'] = $form;
        }

        $view = new View($data);
        $response = $viewHandler->createResponse(new Request, $view, $format);
        $this->assertEquals(var_export($expected, true), $response->getContent());
    }

    public static function createResponseWithoutLocationDataProvider()
    {
        return array(
            'not templating aware no form' => array('json', array('foo' => 'bar')),
            'templating aware no form' => array('html', array('foo' => 'bar')),
            'templating aware and form' => array('html', array('foo' => 'bar', 'form' => array('bla' => 'toto')), 1, true, true),
            'not templating aware and invalid form' => array('json', array('foo' => 'bar', 'form' => array(0 => 'error', 1 => 'error')), 0, false, true, 1, 2),
        );
    }

    /**
     * @dataProvider createResponseDataProvider
     */
    public function testCreateResponse($expected, $format, $transformCalls = 0, $supportCalls = 0, $supported = false)
    {
        $viewHandler = new ViewHandler();
        $viewHandler->registerHandler('html', function($this, $request, $response){return $response;});
        $response = $viewHandler->handle(new Request(), new View(), $format);
        $this->assertEquals($expected, $response->getStatusCode());
    }

    public static function createResponseDataProvider()
    {
        return array(
            'no handler' => array(Codes::HTTP_UNSUPPORTED_MEDIA_TYPE, 'xml', 0, 1),
            'custom handler' => array(200, 'html'),
            'transform called' => array(200, 'json', 1, 1, true),
        );
    }
}

class ViewHandlerProxy extends ViewHandler
{
    public function createResponse(Request $request, View $view, $format)
    {
        return parent::createResponse($request, $view, $format);
    }
}
