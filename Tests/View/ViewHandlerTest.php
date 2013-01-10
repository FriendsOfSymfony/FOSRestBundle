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
use Symfony\Bundle\FrameworkBundle\Templating\TemplateReference;
use FOS\Rest\Util\Codes;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormView;

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
    public function testSupportsFormat($expected, $formats, $customFormatName)
    {
        $viewHandler = new ViewHandler($formats);
        $viewHandler->registerHandler($customFormatName, function(){});

        $this->assertEquals($expected, $viewHandler->supports('html'));
    }

    public static function supportsFormatDataProvider()
    {
        return array(
            'not supported'   => array(false, array('json' => false), 'xml'),
            'html default'   => array(true, array('html' => true), 'xml'),
            'html custom'   => array(true, array('json' => false), 'html'),
            'html both'   => array(true, array('html' => true), 'html'),
        );
    }

    public function testRegisterHandle()
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

    /**
     * @dataProvider getStatusCodeDataProvider
     */
    public function testGetStatusCode($expected, $data, $isBound, $isValid, $isBoundCalled, $isValidCalled, $forceNoContentCode)
    {
        $reflectionMethod = new \ReflectionMethod('\FOS\RestBundle\View\ViewHandler', 'getStatusCode');
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

        if ($data) {
            $data = array('form' => $form);
        }
        $view =  new View($data ? $data : null);

        $viewHandler = new ViewHandler(array(), $expected, $forceNoContentCode);
        $this->assertEquals($expected, $reflectionMethod->invoke($viewHandler, $view));
    }

    public static function getStatusCodeDataProvider()
    {
        return array(
            'no data' => array(Codes::HTTP_OK, false, false, false, 0, 0, 0),
            'no data with 204' => array(Codes::HTTP_NO_CONTENT, false, false, false, 0, 0, 1),
            'form key form not bound' => array(Codes::HTTP_OK, true, false, true, 1, 0, 0),
            'form key form is bound and invalid' => array(403, true, true, false, 1, 1, 0),
            'form key form bound and valid' => array(Codes::HTTP_OK, true, true, true, 1, 1, 0),
            'form key null form bound and valid' => array(Codes::HTTP_OK, true, true, true, 1, 1, 0)
        );
    }

    /**
     * @dataProvider createResponseWithLocationDataProvider
     */
    public function testCreateResponseWithLocation($expected, $format, $forceRedirects, $forceNoContentCode)
    {
        $viewHandler = new ViewHandler(array('html' => true, 'json' => false, 'xml' => false), Codes::HTTP_BAD_REQUEST, $forceNoContentCode, false, $forceRedirects);
        $view = new View();
        $view->setLocation('foo');
        $returnedResponse = $viewHandler->createResponse($view, new Request(), $format);

        $this->assertEquals($expected, $returnedResponse->getStatusCode());
        $this->assertEquals('foo', $returnedResponse->headers->get('location'));
    }

    public static function createResponseWithLocationDataProvider()
    {
        return array(
            'empty force redirects' => array(200, 'xml', array('json' => 403), 0),
            'empty force redirects with 204' => array(204, 'xml', array('json' => 403), 1),
            'force redirects response is redirect' => array(200, 'json', array(), 0),
            'force redirects response not redirect' => array(403, 'json', array('json' => 403), 0),
            'html and redirect' => array(301, 'html', array('html' => 301), 0),
        );
    }

    /**
     * @dataProvider createResponseWithoutLocationDataProvider
     */
    public function testCreateResponseWithoutLocation($format, $expected, $createViewCalls = 0, $formIsValid = false, $form = false)
    {
        $viewHandler = new ViewHandler(array('html' => true, 'json' => false));

        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container', array('get', 'getParameter'));
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
                ->with('fos_rest.templating')
                ->will($this->returnValue($templating));
        } else {
            $serializer = $this->getMockBuilder('\JMS\Serializer\Serializer')
                ->setMethods(array('serialize', 'setExclusionStrategy'))
                ->disableOriginalConstructor()
                ->getMock();
            $serializer
                ->expects($this->once())
                ->method('serialize')
                ->will($this->returnValue(var_export($expected, true)));

            $serializer
                ->expects($this->once())
                ->method('setExclusionStrategy')
                ->will($this->returnValue(null));

            $container
                ->expects($this->once())
                ->method('get')
                ->with('fos_rest.serializer')
                ->will($this->returnValue($serializer));

            $container
                ->expects($this->any())
                ->method('getParameter')
                ->will($this->onConsecutiveCalls('foo', '1.0'));
        }

        $viewHandler->setContainer($container);

        if ($form) {
            $data = $this->getMock('\Symfony\Component\Form\Form', array('createView', 'getData', 'isValid'), array(), '', false);
            $data
                ->expects($this->exactly($createViewCalls))
                ->method('createView')
                ->will($this->returnValue(array('bla' => 'toto')));
            $data
                ->expects($this->exactly($createViewCalls))
                ->method('getData')
                ->will($this->returnValue(array('bla' => 'toto')));
            $data
                ->expects($this->any())
                ->method('isValid')
                ->will($this->returnValue($formIsValid));
        } else {
            $data = array('foo' => 'bar');
        }

        $view = new View($data);
        $response = $viewHandler->createResponse($view, new Request, $format);
        $this->assertEquals(var_export($expected, true), $response->getContent());
    }

    public static function createResponseWithoutLocationDataProvider()
    {
        return array(
            'not templating aware no form' => array('json', array('foo' => 'bar')),
            'templating aware no form' => array('html', array('foo' => 'bar')),
            'templating aware and form' => array('html', array('data' => array('bla' => 'toto')), 1, true, true),
            'not templating aware and invalid form' => array('json', array('data' => array(0 => 'error', 1 => 'error')), 0, false, true),
        );
    }
    /**
     * @dataProvider createShouldSerializeNullDataProvider
     */
    public function testShouldSerializeNull($expected, $shouldSerializeNull)
    {
        $viewHandler = new ViewHandler(array('json' => false), 404, 200, $shouldSerializeNull);
        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container', array('get', 'getParameter'));

        $viewHandler->setContainer($container);

        $serializer = $this->getMockBuilder('\JMS\Serializer\Serializer')
            ->setMethods(array('serialize', 'setExclusionStrategy'))
            ->disableOriginalConstructor()
            ->getMock();

        if ($shouldSerializeNull) {
            $serializer
                ->expects($this->once())
                ->method('serialize')
                ->will($this->returnValue(json_encode(null)));

            $container
                ->expects($this->once())
                ->method('get')
                ->with('fos_rest.serializer')
                ->will($this->returnValue($serializer));
        } else {
            $serializer
                ->expects($this->never())
                ->method('serialize');

            $container
                ->expects($this->never())
                ->method('get');
        }

        $response = $viewHandler->createResponse(new View(), new Request(), 'json');
        $this->assertEquals($expected, $response->getContent());
    }

    public static function createShouldSerializeNullDataProvider()
    {
        return array(
            'should serialize null'     => array("null", true),
            'should not serialize null' => array("", false)
        );
    }

    /**
     * @dataProvider createResponseDataProvider
     */
    public function testCreateResponse($expected, $format, $formats)
    {
        $viewHandler = new ViewHandler($formats);
        $viewHandler->registerHandler('html', function($handler, $view, $request){return $view;});

        $response = $viewHandler->handle(new View(null, $expected), new Request(), $format);
        $this->assertEquals($expected, $response->getStatusCode());
    }

    public static function createResponseDataProvider()
    {
        return array(
            'no handler' => array(Codes::HTTP_UNSUPPORTED_MEDIA_TYPE, 'xml', array()),
            'custom handler' => array(200, 'html', array()),
            'transform called' => array(200, 'json', array('json' => false)),
        );
    }

    public function testHandle()
    {
        $viewHandler = new ViewHandler(array('html' => true));

        $templating = $this->getMockBuilder('\Symfony\Bundle\FrameworkBundle\Templating\PhpEngine')
            ->setMethods(array('render'))
            ->disableOriginalConstructor()
            ->getMock();
        $templating
            ->expects($this->once())
            ->method('render')
            ->will($this->returnValue(''));

        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container', array('get'));
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls(new Request(), $templating));
        $viewHandler->setContainer($container);

        $data = array('foo' => 'bar');

        $view = new View($data);
        $this->assertInstanceOf('\Symfony\Component\HttpFoundation\Response', $viewHandler->handle($view));
    }

    public function testHandleCustom()
    {
        $viewHandler = new ViewHandler(array());
        $viewHandler->registerHandler('html', ($callback = function(){ return 'foo'; }));

        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container', array('get'));
        $container
            ->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue(new Request()));
        $viewHandler->setContainer($container);

        $data = array('foo' => 'bar');

        $view = new View($data);
        $this->assertEquals('foo', $viewHandler->handle($view));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testHandleNotSupported()
    {
        $viewHandler = new ViewHandler(array());

        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container', array('get'));
        $container
            ->expects($this->once())
            ->method('get')
            ->with('request')
            ->will($this->returnValue(new Request()));
        $viewHandler->setContainer($container);

        $data = array('foo' => 'bar');

        $view = new View($data);
        $viewHandler->handle($view);
    }

    /**
     * @dataProvider prepareTemplateParametersDataProvider
     */
    public function testPrepareTemplateParametersWithProvider($viewData, $expected)
    {
        $handler = new ViewHandler();

        $view = new View();
        $view->setData($viewData);

        $this->assertEquals($expected, $handler->prepareTemplateParameters($view));
    }

    public function prepareTemplateParametersDataProvider()
    {
        $object = new \stdClass();

        $formView = new FormView();
        $form = $this->getMockBuilder('\Symfony\Component\Form\Form')
            ->setMethods(array('createView', 'getData'))
            ->disableOriginalConstructor()
            ->getMock();
        $form
            ->expects($this->once())
            ->method('createView')
            ->will($this->returnValue($formView));
        $form
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($formView));

        return array(
            'assoc array does not change'   => array(array('foo' => 'bar'), array('foo' => 'bar')),
            'ordered array is wrapped as data key'  => array(array('foo', 'bar'), array('data' => array('foo', 'bar'))),
            'object is wrapped as data key' => array($object, array('data' => $object)),
            'form is wrapped as form key'   => array($form, array('form' => $formView, 'data' => $formView))
        );
    }
}
