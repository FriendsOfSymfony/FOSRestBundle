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
    Symfony\Bundle\FrameworkBundle\Templating\TemplateReference,
    FOS\RestBundle\Response\Codes,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response;

/**
 * View test
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class ViewTest extends \PHPUnit_Framework_TestCase
{
    public function testSetTemplateTemplateFormat()
    {
        $view = new View();
        
        $view->setTemplate('foo');
        $this->assertEquals('foo', $view->getTemplate());
        
        $view->setTemplate($template = new TemplateReference());
        $this->assertEquals($template, $view->getTemplate());
        
        try {
            $view->setTemplate(array());
            $this->fail('->setTemplate() should accept strings and TemplateReference instances only');
        } catch (\InvalidArgumentException $e) {            
        }
    }

    public function testSetContainer() {
        $view = new View();
        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container', array(), array(), '', false);
        $view->setContainer($container);
        $this->assertAttributeEquals($container, 'container', $view);

        try {
            $view->setContainer(new \stdClass());
            $this->fail(__METHOD__.' should only accept objects implementing ContainerInterface');
        } catch (\Exception $e) {
        }
    }

    /**
     * @dataProvider supportsFormatDataProvider
     */
    public function testSupportsFormat($expected, $formatName, $customFormatName) {
        $view = new View(array($formatName));
        $view->registerHandler($customFormatName, function(){});

        $this->assertEquals($expected, $view->supports('html'));
    }

    public static function supportsFormatDataProvider() {
        return array(
            'not supported'   => array(false, 'json', 'xml'),
            'html default'   => array(true, 'html', 'xml'),
            'html custom'   => array(true, 'json', 'html'),
            'html both'   => array(true, 'html', 'html'),
        );
    }

    public function testRegsiterHandle() {
        $view = new View();
        $view->registerHandler('html', ($callback = function(){}));
        $this->assertAttributeEquals(array('html' => $callback), 'customHandlers', $view);
    }

    public function testRegisterHandleExpectsException() {
        $view = new View();
        try {
            $view->registerHandler('json', new \stdClass());
            $this->fail(__METHOD__. ' should only accept callables as second argument');
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Registered view callback must be callable.', $e->getMessage());
        }
    }

    public function testSetResourceRoute() {
        $route = $this->getMock('stdClass', array('generate'));
        $route
            ->expects($this->exactly(2))
            ->method('generate')
            ->will($this->returnArgument(0));

        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container', array('get'));
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue($route));

        $view = new View();
        $view->setContainer($container);
        $routeName = 'users';
        $code = 500;

        $view->setResourceRoute($routeName);
        $this->assertAttributeEquals($routeName, 'location', $view);
        $this->assertAttributeEquals(Codes::HTTP_CREATED, 'code', $view);

        $view->setResourceRoute($routeName, array(), $code);
        $this->assertAttributeEquals($routeName, 'location', $view);
        $this->assertAttributeEquals($code, 'code', $view);
    }

    public function testSetRedirectRoute() {
        $route = $this->getMock('stdClass', array('generate'));
        $route
            ->expects($this->exactly(2))
            ->method('generate')
            ->will($this->returnArgument(0));

        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container', array('get'));
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->returnValue($route));

        $view = new View();
        $view->setContainer($container);
        $routeName = 'users';
        $code = 500;

        $view->setRedirectRoute($routeName);
        $this->assertAttributeEquals($routeName, 'location', $view);
        $this->assertAttributeEquals(Codes::HTTP_FOUND, 'code', $view);

        $view->setRedirectRoute($routeName, array(), $code);
        $this->assertAttributeEquals($routeName, 'location', $view);
        $this->assertAttributeEquals($code, 'code', $view);
    }

    public function testSetLocation() {
        $view = new View();
        $location = 'location';
        $view->setLocation($location);
        $this->assertEquals($location, $view->getLocation());

        try {
            $view->setLocation('');
            $this->fail();
        } catch (\InvalidArgumentException $e) {
            $this->assertEquals('Cannot redirect to an empty URL.', $e->getMessage());
        }
    }

    public function testSetFailedValidationStatusCode() {
        $view = new View(null, 403);
        $this->assertNull($view->getStatusCode());
        $view->setFailedValidationStatusCode();
        $this->assertEquals(403, $view->getStatusCode());
    }

    public function testSetFormKey() {
        $view = new View();
        $key = 'foo';
        $view->setFormKey($key);
        $this->assertAttributeEquals($key, 'formKey', $view);
    }

    /**
     * @dataProvider getStatusCodeFromParametersDataProvider
     */
    public function testGetStatusCodeFromParameters($expected, $key = false, $isBound = false, $isValid = false, $isBoundCalled = 0, $isValidCalled = 0) {
        $reflectionMethod = new \ReflectionMethod('\FOS\RestBundle\View\View', 'getStatusCodeFromParameters');
        $reflectionMethod->setAccessible(true);

        $form = $this->getMock('stdClass', array('isBound', 'isValid'));
        $form
            ->expects($this->exactly($isBoundCalled))
            ->method('isBound')
            ->will($this->returnValue($isBound));
        $form
            ->expects($this->exactly($isValidCalled))
            ->method('isValid')
            ->will($this->returnValue($isValid));

        $view = $this->getMock('\FOS\RestBundle\View\View', array('assignFormKey', 'getParameters'), array(null, 403));
        $view
            ->expects($this->any())
            ->method('assignFormKey')
            ->will($this->returnValue($form));
        $view
            ->expects($this->any())
            ->method('getParameters')
            ->will($this->returnValue(array()));

        $view->setFormKey($key);
        $this->assertEquals($expected, $reflectionMethod->invoke($view));
    }

    public static function getStatusCodeFromParametersDataProvider() {
        return array(
            'no form key' => array(Codes::HTTP_OK),
            'form key form not bound' => array(Codes::HTTP_OK, 'foo', false, true, 1),
            'form key form is bound and invalid' => array(403, 'foo', true, false, 1, 1),
            'form key form bound and valid' => array(Codes::HTTP_OK, 'foo', true, true, 1, 1),
        );
    }

    /**
     * @dataProvider assignFormKeyDataProvider
     */
    public function testAssignFormKey($formKey, $parameterIndex) {
        $form = null;
        $parameters = null;
        if ($parameterIndex) {
            $form = $this->getMock('\Symfony\Component\Form\Form', array(), array(), '', false);
            $parameters[$parameterIndex] = $form;
        }
        $view = new ViewProxy();
        $view->setFormKey($formKey);
        $this->assertEquals($form, $view->assignFormKey($parameters));
        $this->assertAttributeEquals($parameterIndex, 'formKey', $view);
    }

    public static function assignFormKeyDataProvider() {
        return array(
            'no parameters' => array(null, false),
            'form key is null' => array(null, 'form'),
            'form key is index' => array('form', 'form'),
        );
    }

    /**
     * @dataProvider setParametersDataProvider
     */
    public function testSetParameters($parameters) {
        $view = new View();
        $view->setParameters($parameters);
        $this->assertEquals($parameters, $view->getParameters());
    }

    public static function setParametersDataProvider() {
        return array(
            'null as parameters' => array(null),
            'array as parameters' => array(array('foo' => 'bar')),
        );
    }

    public function testSetEngine() {
        $view = new View();
        $engine = 'bar';
        $view->setEngine($engine);
        $this->assertEquals($engine, $view->getEngine());
    }

    public function testSetFormat() {
        $view = new View();
        $format = 'bar';
        $view->setFormat($format);
        $this->assertEquals($format, $view->getFormat());
    }

    public function testSetSerializer() {
        $serializer = $this->getMock('\Symfony\Component\Serializer\Serializer', array(), array(), '', false);
        $view = new View();

        $view->setSerializer();
        $this->assertAttributeEquals(null, 'serializer', $view);

        $view->setSerializer($serializer);
        $this->assertEquals($serializer, $view->getSerializer());

        try {
            $view->setSerializer(new \stdClass());
            $this->fail();
        } catch (\Exception $e) {
        }
    }

    public function testGetSerializer() {
        $containerSerializer = $serializer = $this->getMock('\Symfony\Component\Serializer\Serializer', array(), array(), '', false);
        $serializer = $serializer = $this->getMock('\Symfony\Component\Serializer\Serializer', array(), array(), '', false);

        $container = $this->getMock('\Symfony\Component\DependencyInjection\Container', array('get'));
        $container
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($containerSerializer));

        $view = new View();
        $view->setContainer($container);

        $this->assertEquals($containerSerializer, $view->getSerializer());
        $view->setSerializer($serializer);
        $this->assertEquals($serializer, $view->getSerializer());
    }

    /**
     * @dataProvider transformWithLocationDataProvider
     */
    public function testTransformWithLocation($expected, $origStatusCode, $format, $isRedirectCalls = 0, $isRedirect = false, $setContentCalls = 0) {
        $response = $this->getMock('\Symfony\Component\HttpFoundation\Response', array('isRedirect', 'setContent'));
        $response
            ->expects($this->exactly($isRedirectCalls))
            ->method('isRedirect')
            ->will($this->returnValue($isRedirect));
        $response
            ->expects($this->exactly($setContentCalls))
            ->method('setContent');
        $response->setStatusCode($origStatusCode);

        $view = new ViewProxy(null, Codes::HTTP_BAD_REQUEST, 'form', array('json' => 403));
        $view->setLocation('foo');
        $returnedResponse = $view->transform(new \Symfony\Component\HttpFoundation\Request(), $response, $format);
        $this->assertEquals($expected, $returnedResponse->getStatusCode());
        $this->assertEquals('foo', $response->headers->get('location'));
    }
    
    public static function transformWithLocationDataProvider() {
        return array(
            'empty forceredirects' => array(200, 200, 'xml'),
            'forceredirects response is redirect' => array(200, 200, 'json', 1, true),
            'forceredirects response not redirect' => array(403, 200, 'json', 1),
            'html and redirect' => array(301, 301, 'html', 1, true, 1),
        );
    }

    /**
     * @dataProvider transformWithoutLocationDataProvider
     */
    public function testTransformWithoutLocation($expected, $encoderClass, $setTemplateCalls = 0, $createViewCalls = 0, $formIsValid = false, $formKey = null, $getChildrenCalls = 0, $getErrorsCalls = 0) {
        $encoder = $this->getMock($encoderClass, array('setTemplate'));
        $encoder
            ->expects($this->exactly($setTemplateCalls))
            ->method('setTemplate');

        $serializer = $this->getMock('\stdClass', array('serialize', 'getEncoder'));
        $serializer
            ->expects($this->any())
            ->method('getEncoder')
            ->will($this->returnValue($encoder));
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnArgument(0));

        $child = $this->getMock('\stdClass', array('getErrors'));
        $child
            ->expects($this->exactly($getErrorsCalls))
            ->method('getErrors')
            ->will($this->returnValue('error'));

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
        $view = $this->getMock('\FOS\RestBundle\Tests\View\ViewProxy', array('getSerializer'));
        $view
            ->expects($this->any())
            ->method('getSerializer')
            ->will($this->returnValue($serializer));
        $parameters = array('foo' => 'bar');
        if ($formKey) {
            $parameters[$formKey] = $form;
        }
        $view->setFormKey($formKey);
        $view->setParameters($parameters);
        $response = $view->transform(new Request, new Response(), 'html');
        $this->assertEquals($expected, $response->getContent());
    }

    public static function transformWithoutLocationDataProvider() {
        return array(
            'not templating aware no form' => array(array('foo' => 'bar'), '\stdClass'),
            'templating aware no form' => array(array('foo' => 'bar'), '\FOS\RestBundle\Serializer\Encoder\HtmlEncoder', 1),
            'templating aware and form' => array(array('foo' => 'bar', 'form' => array('bla' => 'toto')), '\FOS\RestBundle\Serializer\Encoder\HtmlEncoder', 1, 1, false, 'form'),
            'not templating aware and invalid form' => array(array('foo' => 'bar', 'form' => array(0 => 'error', 1 => 'error')), '\stdClass', 0, 0, false, 'form', 1, 2),
        );
    }
}

class ViewProxy extends View {
    public function assignFormKey($parameters) {
        return parent::assignFormKey($parameters);
    }

    public function transform(Request $request, Response $response, $format) {
        return parent::transform($request, $response, $format);
    }
}
