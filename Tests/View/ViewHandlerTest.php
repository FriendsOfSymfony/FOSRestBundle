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

use FOS\RestBundle\Serializer\ExceptionWrapperSerializeHandler;
use FOS\RestBundle\Util\ExceptionWrapper;
use FOS\RestBundle\View\ExceptionWrapperHandler;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use JMS\Serializer\Handler\FormErrorHandler;
use JMS\Serializer\Handler\HandlerRegistry;
use JMS\Serializer\SerializerBuilder;
use FOS\RestBundle\Util\Codes;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Forms;
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
        $viewHandler->registerHandler($customFormatName, function () {});

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
        $viewHandler->registerHandler('html', ($callback = function () {}));
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
    public function testGetStatusCode($expected, $data, $isSubmitted, $isValid, $isSubmittedCalled, $isValidCalled, $noContentCode)
    {
        $reflectionMethod = new \ReflectionMethod('FOS\RestBundle\View\ViewHandler', 'getStatusCode');
        $reflectionMethod->setAccessible(true);

        $form = $this->getMock('Symfony\Component\Form\Form', array('isSubmitted', 'isValid'), array(), '', false);
        $form
            ->expects($this->exactly($isSubmittedCalled))
            ->method('isSubmitted')
            ->will($this->returnValue($isSubmitted));
        $form
            ->expects($this->exactly($isValidCalled))
            ->method('isValid')
            ->will($this->returnValue($isValid));

        if ($data) {
            $data = array('form' => $form);
        }
        $view = new View($data ? $data : null);

        $viewHandler = new ViewHandler(array(), $expected, $noContentCode);
        $this->assertEquals($expected, $reflectionMethod->invoke($viewHandler, $view, $view->getData()));
    }

    public static function getStatusCodeDataProvider()
    {
        return array(
            'no data' => array(Codes::HTTP_OK, false, false, false, 0, 0, Codes::HTTP_OK),
            'no data with 204' => array(Codes::HTTP_NO_CONTENT, false, false, false, 0, 0, Codes::HTTP_NO_CONTENT),
            'form key form not bound' => array(Codes::HTTP_OK, true, false, true, 1, 0, Codes::HTTP_OK),
            'form key form is bound and invalid' => array(403, true, true, false, 1, 1, Codes::HTTP_OK),
            'form key form bound and valid' => array(Codes::HTTP_OK, true, true, true, 1, 1, Codes::HTTP_OK),
            'form key null form bound and valid' => array(Codes::HTTP_OK, true, true, true, 1, 1, Codes::HTTP_OK),
        );
    }

    /**
     * @dataProvider createResponseWithLocationDataProvider
     */
    public function testCreateResponseWithLocation($expected, $format, $forceRedirects, $noContentCode)
    {
        $viewHandler = new ViewHandler(array('html' => true, 'json' => false, 'xml' => false), Codes::HTTP_BAD_REQUEST, $noContentCode, false, $forceRedirects);
        $view = new View();
        $view->setLocation('foo');
        $returnedResponse = $viewHandler->createResponse($view, new Request(), $format);

        $this->assertEquals($expected, $returnedResponse->getStatusCode());
        $this->assertEquals('foo', $returnedResponse->headers->get('location'));
    }

    public static function createResponseWithLocationDataProvider()
    {
        return array(
            'empty force redirects' => array(200, 'xml', array('json' => 403), Codes::HTTP_OK),
            'empty force redirects with 204' => array(204, 'xml', array('json' => 403), Codes::HTTP_NO_CONTENT),
            'force redirects response is redirect' => array(200, 'json', array(), Codes::HTTP_OK),
            'force redirects response not redirect' => array(403, 'json', array('json' => 403), Codes::HTTP_OK),
            'html and redirect' => array(301, 'html', array('html' => 301), Codes::HTTP_OK),
        );
    }

    public function testCreateResponseWithLocationAndData()
    {
        $testValue = array('naviter' => 'oudie');
        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));
        $this->setupMockedSerializer($container, $testValue);

        $viewHandler = new ViewHandler(array('json' => false));
        $viewHandler->setContainer($container);

        $view = new View();
        $view->setStatusCode(Codes::HTTP_CREATED);
        $view->setLocation('foo');
        $view->setData($testValue);
        $returnedResponse = $viewHandler->createResponse($view, new Request(), 'json');

        $this->assertEquals('foo', $returnedResponse->headers->get('location'));
        $this->assertEquals(var_export($testValue, true), $returnedResponse->getContent());
    }

    public function testCreateResponseWithRoute()
    {
        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));

        $doRoute = function ($name, $parameters) {
            $route = '/';
            foreach ($parameters as $name => $value) {
                $route .= sprintf('%s/%s/', $name, $value);
            }

            return $route;
        };

        $router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')
            ->getMock();

        $router
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback($doRoute));

        $container
            ->expects($this->any())
            ->method('get')
            ->with('fos_rest.router')
            ->will($this->returnValue($router));

        $viewHandler = new ViewHandler(array('json' => false));
        $viewHandler->setContainer($container);

        $view = new View();
        $view->setStatusCode(Codes::HTTP_CREATED);
        $view->setRoute('foo');
        $view->setRouteParameters(array('id' => 2));
        $returnedResponse = $viewHandler->createResponse($view, new Request(), 'json');

        $this->assertEquals('/id/2/', $returnedResponse->headers->get('location'));
    }

    public function testShouldReturnErrorResponseWhenDataContainsFormAndFormIsNotValid()
    {
        $container = new Container();

        $serializer = $this->getMock('JMS\Serializer\Serializer', array(), array(), '', false);
        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnCallback(function ($data) {
                return serialize($data);
            }))
        ;

        $container->set('fos_rest.serializer', $serializer);
        $container->set('fos_rest.exception_handler', new ExceptionWrapperHandler());

        //test
        $viewHandler = new ViewHandler(null, $expectedFailedValidationCode = Codes::HTTP_I_AM_A_TEAPOT);
        $viewHandler->setContainer($container);

        $form = $this->getMock('Symfony\\Component\\Form\\Form', array('createView', 'getData', 'isValid', 'isSubmitted'), array(), '', false);
        $form
            ->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue(false));
        $form
            ->expects($this->any())
            ->method('isSubmitted')
            ->will($this->returnValue(true));

        $view = new View($form);
        $response = $viewHandler->createResponse($view, new Request(), 'json');

        $data = unserialize($response->getContent());
        $this->assertInstanceOf('FOS\\RestBundle\\Util\\ExceptionWrapper', $data);
        $this->assertEquals('Validation Failed', $this->readAttribute($data, 'message'));
        $this->assertInstanceOf('Symfony\\Component\\Form\\Form', $this->readAttribute($data, 'errors'));
        $this->assertEquals($expectedFailedValidationCode, $this->readAttribute($data, 'code'));
    }

    /**
     * @dataProvider createResponseWithoutLocationDataProvider
     */
    public function testCreateResponseWithoutLocation($format, $expected, $createViewCalls = 0, $formIsValid = false, $form = false)
    {
        $viewHandler = new ViewHandler(array('html' => true, 'json' => false));

        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));
        if ('html' === $format) {
            $templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\PhpEngine')
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
            $this->setupMockedSerializer($container, $expected);
        }

        $viewHandler->setContainer($container);

        if ($form) {
            $data = $this->getMock('Symfony\Component\Form\Form', array('createView', 'getData', 'isValid', 'isSubmitted'), array(), '', false);
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
            $data
                ->expects($this->any())
                ->method('isSubmitted')
                ->will($this->returnValue(true));
        } else {
            $data = array('foo' => 'bar');
        }

        $view = new View($data);
        $response = $viewHandler->createResponse($view, new Request(), $format);
        $this->assertEquals(var_export($expected, true), $response->getContent());
    }

    private function setupMockedSerializer($container, $expected)
    {
        $serializer = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->setMethods(array('serialize'))
            ->disableOriginalConstructor()
            ->getMock();

        $serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue(var_export($expected, true)));

        $container
            ->expects($this->any())
            ->method('get')
            ->with($this->logicalOr(
                  $this->equalTo('fos_rest.serializer'),
                  $this->equalTo('fos_rest.exception_handler')
              ))
            ->will(
                  $this->returnCallback(
                      function ($method) use ($serializer) {
                            switch ($method) {
                                case 'fos_rest.serializer':
                                    return $serializer;
                                case 'fos_rest.exception_handler':
                                    return new ExceptionWrapperHandler();
                            }
                      }
                  )
              );
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
     * @dataProvider createSerializeNullDataProvider
     */
    public function testSerializeNull($expected, $serializeNull)
    {
        $viewHandler = new ViewHandler(array('json' => false), 404, 200, $serializeNull);
        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));

        $viewHandler->setContainer($container);

        $serializer = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->setMethods(array('serialize', 'setExclusionStrategy'))
            ->disableOriginalConstructor()
            ->getMock();

        if ($serializeNull) {
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

    public static function createSerializeNullDataProvider()
    {
        return array(
            'should serialize null'     => array("null", true),
            'should not serialize null' => array("", false),
        );
    }

    /**
     * @dataProvider createSerializeNullDataValuesDataProvider
     */
    public function testSerializeNullDataValues($expected, $serializeNull)
    {
        $viewHandler = new ViewHandler(array('json' => false), 404, 200);
        $viewHandler->setSerializeNullStrategy($serializeNull);

        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));

        $viewHandler->setContainer($container);
        $contextMethod = new \ReflectionMethod($viewHandler, 'getSerializationContext');
        $contextMethod->setAccessible(true);

        $view = new View();
        $context = $contextMethod->invoke($viewHandler, $view);
        $this->assertEquals($expected, $context->shouldSerializeNull());
    }

    public static function createSerializeNullDataValuesDataProvider()
    {
        return array(
            'should serialize null values'     => array(true, true),
            'should not serialize null values' => array(false, false),
        );
    }

    /**
     * @dataProvider createResponseDataProvider
     */
    public function testCreateResponse($expected, $formats)
    {
        $viewHandler = new ViewHandler($formats);
        $viewHandler->registerHandler('html', function ($handler, $view) { return $view; });

        $response = $viewHandler->handle(new View(null, $expected), new Request());
        $this->assertEquals($expected, $response->getStatusCode());
    }

    public static function createResponseDataProvider()
    {
        return array(
            'no handler' => array(Codes::HTTP_UNSUPPORTED_MEDIA_TYPE, array()),
            'custom handler' => array(200, array()),
            'transform called' => array(200, array('json' => false)),
        );
    }

    public function testHandle()
    {
        $viewHandler = new ViewHandler(array('html' => true));

        $templating = $this->getMockBuilder('Symfony\Bundle\FrameworkBundle\Templating\PhpEngine')
            ->setMethods(array('render'))
            ->disableOriginalConstructor()
            ->getMock();
        $templating
            ->expects($this->once())
            ->method('render')
            ->will($this->returnValue(''));

        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));
        $container
            ->expects($this->exactly(2))
            ->method('get')
            ->will($this->onConsecutiveCalls(new Request(), $templating));
        $viewHandler->setContainer($container);

        $data = array('foo' => 'bar');

        $view = new View($data);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $viewHandler->handle($view));
    }

    public function testHandleCustom()
    {
        $viewHandler = new ViewHandler(array());
        $viewHandler->registerHandler('html', ($callback = function () { return 'foo'; }));

        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));
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

        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));
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
    public function testPrepareTemplateParametersWithProvider($viewData, $templateData, $expected)
    {
        $handler = new ViewHandler(array('html' => true));

        $view = new View();
        $view->setFormat('html');
        $view->setData($viewData);

        if (null !== $templateData)
            $view->setTemplateData($templateData);

        $this->assertEquals($expected, $handler->prepareTemplateParameters($view));
    }

    public function prepareTemplateParametersDataProvider()
    {
        $object = new \stdClass();

        $formView = new FormView();
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
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

        $self = $this;

        return array(
            'assoc array does not change'   => array(array('foo' => 'bar'), null, array('foo' => 'bar')),
            'ordered array is wrapped as data key'  => array(array('foo', 'bar'), null, array('data' => array('foo', 'bar'))),
            'object is wrapped as data key' => array($object, null, array('data' => $object)),
            'form is wrapped as form key'   => array($form, null, array('form' => $formView, 'data' => $formView)),
            'template data is added to data'   => array(array('foo' => 'bar'), array('baz' => 'qux'), array('foo' => 'bar', 'baz' => 'qux')),
            'lazy template data is added to data'   => array(
                array('foo' => 'bar'),
                function() { return array('baz' => 'qux'); },
                array('foo' => 'bar', 'baz' => 'qux')
            ),
            'lazy template data have reference to viewhandler and view'   => array(
                array('foo' => 'bar'),
                function ($handler, $view) use ($self) {
                    $self->assertInstanceOf('FOS\\RestBundle\\View\\ViewHandlerInterface', $handler);
                    $self->assertInstanceOf('FOS\\RestBundle\\View\\View', $view);
                    $self->assertTrue($handler->isFormatTemplating($view->getFormat()));
                    return array('format' => $view->getFormat());
                },
                array('foo' => 'bar', 'format' => 'html')
            ),
        );
    }

    public function testConfigurableViewHandlerInterface()
    {
        //test
        $viewHandler = new ViewHandler();
        $viewHandler->setExclusionStrategyGroups('bar');
        $viewHandler->setExclusionStrategyVersion('1.1');
        $viewHandler->setSerializeNullStrategy(true);

        $contextMethod = new \ReflectionMethod($viewHandler, 'getSerializationContext');
        $contextMethod->setAccessible(true);

        $view = new View();
        $context = $contextMethod->invoke($viewHandler, $view);
        $this->assertEquals(array('bar'), $context->attributes->get('groups')->getOrThrow(new \Exception('Serialization groups not set as expected')));
        $this->assertEquals('1.1', $context->attributes->get('version')->getOrThrow(new \Exception('Serialization version not set as expected')));
        $this->assertTrue($context->shouldSerializeNull());
    }

    /**
     * @dataProvider exceptionWrapperSerializeResponseContentProvider
     * @param string $format
     */
    public function testCreateResponseWithFormErrorsAndSerializationGroups($format)
    {
        $form = Forms::createFormFactory()->createBuilder()
            ->add('name', 'text')
            ->add('description', 'text')
            ->getForm();

        $form->get('name')->addError(new FormError('Invalid name'));

        $exceptionWrapper = new ExceptionWrapper(
            array(
                'status_code' => 400,
                'message' => 'Validation Failed',
                'errors' => $form,
            )
        );

        $view = new View($exceptionWrapper);
        $view->getSerializationContext()->setGroups(array('Custom'));

        $wrapperHandler = new ExceptionWrapperSerializeHandler();
        $translatorMock = $this->getMock(
            'Symfony\\Component\\Translation\\TranslatorInterface',
            array('trans', 'transChoice', 'setLocale', 'getLocale')
        );
        $translatorMock
            ->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));

        $formErrorHandler = new FormErrorHandler($translatorMock);

        $serializer = SerializerBuilder::create()
            ->configureHandlers(function (HandlerRegistry $handlerRegistry) use ($wrapperHandler, $formErrorHandler) {
                $handlerRegistry->registerSubscribingHandler($wrapperHandler);
                $handlerRegistry->registerSubscribingHandler($formErrorHandler);
            })
            ->build();

        $container = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));
        $container
            ->expects($this->once())
            ->method('get')
            ->with('fos_rest.serializer')
            ->will($this->returnValue($serializer));

        $viewHandler = new ViewHandler(array());
        $viewHandler->setContainer($container);

        $response = $viewHandler->createResponse($view, new Request(), $format);

        $serializer2 = SerializerBuilder::create()
            ->configureHandlers(function (HandlerRegistry $handlerRegistry) use ($wrapperHandler, $formErrorHandler) {
                $handlerRegistry->registerSubscribingHandler($formErrorHandler);
            })
            ->build();

        $container2 = $this->getMock('Symfony\Component\DependencyInjection\Container', array('get'));
        $container2
            ->expects($this->once())
            ->method('get')
            ->with('fos_rest.serializer')
            ->will($this->returnValue($serializer2));

        $viewHandler = new ViewHandler(array());
        $viewHandler->setContainer($container2);

        $view2 = new View($exceptionWrapper);
        $response2 = $viewHandler->createResponse($view2, new Request(), $format);

        $this->assertEquals($response->getContent(), $response2->getContent());
    }

    /**
     * @return array
     */
    public function exceptionWrapperSerializeResponseContentProvider()
    {
        return array(
            'json' => array('json'),
            'xml' => array('xml'),
        );
    }
}
