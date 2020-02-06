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
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * View test.
 *
 * @author Victor Berchet <victor@suumit.com>
 */
class ViewHandlerTest extends TestCase
{
    private $router;
    private $serializer;
    private $requestStack;

    protected function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\RouterInterface')->getMock();
        $this->serializer = $this->getMockBuilder('FOS\RestBundle\Serializer\Serializer')->getMock();
        $this->requestStack = new RequestStack();
    }

    /**
     * @dataProvider supportsFormatDataProvider
     */
    public function testSupportsFormat($expected, $formats, $customFormatName)
    {
        $viewHandler = $this->createViewHandler($formats);
        $viewHandler->registerHandler($customFormatName, function () {
        });

        $this->assertEquals($expected, $viewHandler->supports('html'));
    }

    public static function supportsFormatDataProvider()
    {
        return [
            'not supported' => [false, ['json' => false], 'xml'],
            'html default' => [true, ['html' => true], 'xml'],
            'html custom' => [true, ['json' => false], 'html'],
            'html both' => [true, ['html' => true], 'html'],
        ];
    }

    public function testRegisterHandle()
    {
        $viewHandler = $this->createViewHandler();
        $viewHandler->registerHandler('html', ($callback = function () {
        }));
        $this->assertAttributeEquals(['html' => $callback], 'customHandlers', $viewHandler);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testRegisterHandleExpectsException()
    {
        $viewHandler = $this->createViewHandler();

        $viewHandler->registerHandler('json', new \stdClass());
    }

    /**
     * @dataProvider getStatusCodeDataProvider
     */
    public function testGetStatusCode(
        $expected,
        $data,
        $isSubmitted,
        $isValid,
        $isSubmittedCalled,
        $isValidCalled,
        $noContentCode,
        $actualStatusCode = null
    ) {
        $reflectionMethod = new \ReflectionMethod(ViewHandler::class, 'getStatusCode');
        $reflectionMethod->setAccessible(true);

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->setMethods(array('isSubmitted', 'isValid'))
            ->getMock();
        $form
            ->expects($this->exactly($isSubmittedCalled))
            ->method('isSubmitted')
            ->will($this->returnValue($isSubmitted));
        $form
            ->expects($this->exactly($isValidCalled))
            ->method('isValid')
            ->will($this->returnValue($isValid));

        if ($data) {
            $data = ['form' => $form];
        }
        $view = new View($data ?: null, $actualStatusCode ?: null);

        $viewHandler = $this->createViewHandler([], $expected, $noContentCode);
        $this->assertEquals($expected, $reflectionMethod->invoke($viewHandler, $view, $view->getData()));
    }

    public static function getStatusCodeDataProvider()
    {
        return [
            'no data' => [Response::HTTP_OK, false, false, false, 0, 0, Response::HTTP_OK],
            'no data with 204' => [Response::HTTP_NO_CONTENT, false, false, false, 0, 0, Response::HTTP_NO_CONTENT],
            'no data, but custom response code' => [Response::HTTP_OK, false, false, false, 0, 0, Response::HTTP_NO_CONTENT, Response::HTTP_OK],
            'form key form not bound' => [Response::HTTP_OK, true, false, true, 1, 0, Response::HTTP_OK],
            'form key form is bound and invalid' => [Response::HTTP_FORBIDDEN, true, true, false, 1, 1, Response::HTTP_OK],
            'form key form bound and valid' => [Response::HTTP_OK, true, true, true, 1, 1, Response::HTTP_OK],
            'form key null form bound and valid' => [Response::HTTP_OK, true, true, true, 1, 1, Response::HTTP_OK],
        ];
    }

    /**
     * @dataProvider createResponseWithLocationDataProvider
     */
    public function testCreateResponseWithLocation($expected, $format, $forceRedirects, $noContentCode)
    {
        $viewHandler = $this->createViewHandler(['html' => true, 'json' => false, 'xml' => false], Response::HTTP_BAD_REQUEST, $noContentCode, false, $forceRedirects);
        $view = new View();
        $view->setLocation('foo');
        $returnedResponse = $viewHandler->createResponse($view, new Request(), $format);

        $this->assertEquals($expected, $returnedResponse->getStatusCode());
        $this->assertEquals('foo', $returnedResponse->headers->get('location'));
    }

    public static function createResponseWithLocationDataProvider()
    {
        return [
            'empty force redirects' => [200, 'xml', ['json' => 403], Response::HTTP_OK],
            'empty force redirects with 204' => [204, 'xml', ['json' => 403], Response::HTTP_NO_CONTENT],
            'force redirects response is redirect' => [200, 'json', [], Response::HTTP_OK],
            'force redirects response not redirect' => [403, 'json', ['json' => 403], Response::HTTP_OK],
            'html and redirect' => [301, 'html', ['html' => 301], Response::HTTP_OK],
        ];
    }

    public function testCreateResponseWithLocationAndData()
    {
        $testValue = ['naviter' => 'oudie'];
        $this->setupMockedSerializer($testValue);

        $viewHandler = $this->createViewHandler(['json' => false]);

        $view = new View();
        $view->setStatusCode(Response::HTTP_CREATED);
        $view->setLocation('foo');
        $view->setData($testValue);
        $returnedResponse = $viewHandler->createResponse($view, new Request(), 'json');

        $this->assertEquals('foo', $returnedResponse->headers->get('location'));
        $this->assertEquals(var_export($testValue, true), $returnedResponse->getContent());
    }

    public function testCreateResponseWithRoute()
    {
        $doRoute = function ($name, $parameters) {
            $route = '/';
            foreach ($parameters as $name => $value) {
                $route .= sprintf('%s/%s/', $name, $value);
            }

            return $route;
        };

        $this->router
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnCallback($doRoute));

        $viewHandler = $this->createViewHandler(['json' => false]);

        $view = new View();
        $view->setStatusCode(Response::HTTP_CREATED);
        $view->setRoute('foo');
        $view->setRouteParameters(['id' => 2]);
        $returnedResponse = $viewHandler->createResponse($view, new Request(), 'json');

        $this->assertEquals('/id/2/', $returnedResponse->headers->get('location'));
    }

    public function testShouldReturnErrorResponseWhenDataContainsFormAndFormIsNotValid()
    {
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnCallback(function ($data, $format, $context) {
                return serialize(array($data, $context));
            }));

        //test
        $viewHandler = $this->createViewHandler(null, $expectedFailedValidationCode = Response::HTTP_I_AM_A_TEAPOT);

        $form = $this->getMockBuilder('Symfony\\Component\\Form\\Form')
            ->disableOriginalConstructor()
            ->setMethods(array('createView', 'getData', 'isValid', 'isSubmitted'))
            ->getMock();
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

        list($data, $context) = unserialize($response->getContent());
        $this->assertInstanceOf('Symfony\\Component\\Form\\Form', $data);
        $this->assertEquals($expectedFailedValidationCode, $context->getAttribute('status_code'));
    }

    /**
     * @dataProvider createResponseWithoutLocationDataProvider
     */
    public function testCreateResponseWithoutLocation($format, $expected, $createViewCalls = 0, $formIsValid = false, $form = false)
    {
        $viewHandler = $this->createViewHandler(['html' => true, 'json' => false]);

        $this->setupMockedSerializer($expected);

        if ($form) {
            $data = $this->getMockBuilder('Symfony\Component\Form\Form')
                ->disableOriginalConstructor()
                ->setMethods(array('createView', 'getData', 'isValid', 'isSubmitted'))
                ->getMock();
            $data
                ->expects($this->exactly($createViewCalls))
                ->method('createView')
                ->will($this->returnValue(['bla' => 'toto']));
            $data
                ->expects($this->exactly($createViewCalls))
                ->method('getData')
                ->will($this->returnValue(['bla' => 'toto']));
            $data
                ->expects($this->any())
                ->method('isValid')
                ->will($this->returnValue($formIsValid));
            $data
                ->expects($this->any())
                ->method('isSubmitted')
                ->will($this->returnValue(true));
        } else {
            $data = ['foo' => 'bar'];
        }

        $view = new View($data);
        $response = $viewHandler->createResponse($view, new Request(), $format);
        $this->assertEquals(var_export($expected, true), $response->getContent());
    }

    private function setupMockedSerializer($expected)
    {
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->will($this->returnValue(var_export($expected, true)));
    }

    public static function createResponseWithoutLocationDataProvider()
    {
        return [
            'not templating aware no form' => ['json', ['foo' => 'bar']],
            'not templating aware and invalid form' => ['json', ['data' => [0 => 'error', 1 => 'error']], 0, false, true],
        ];
    }

    /**
     * @dataProvider createSerializeNullDataProvider
     */
    public function testSerializeNull($expected, $serializeNull)
    {
        $viewHandler = $this->createViewHandler(['json' => false], 404, 200, $serializeNull);

        if ($serializeNull) {
            $this->serializer
                ->expects($this->once())
                ->method('serialize')
                ->will($this->returnValue(json_encode(null)));
        } else {
            $this->serializer
                ->expects($this->never())
                ->method('serialize');
        }

        $response = $viewHandler->createResponse(new View(), new Request(), 'json');
        $this->assertEquals($expected, $response->getContent());
    }

    public static function createSerializeNullDataProvider()
    {
        return [
            'should serialize null' => ['null', true],
            'should not serialize null' => ['', false],
        ];
    }

    /**
     * @dataProvider createSerializeNullDataValuesDataProvider
     */
    public function testSerializeNullDataValues($expected, $serializeNull)
    {
        $viewHandler = $this->createViewHandler(['json' => false], 404, 200);
        $viewHandler->setSerializeNullStrategy($serializeNull);

        $contextMethod = new \ReflectionMethod($viewHandler, 'getSerializationContext');
        $contextMethod->setAccessible(true);

        $view = new View();
        $context = $contextMethod->invoke($viewHandler, $view);
        $this->assertEquals($expected, $context->getSerializeNull());
    }

    public static function createSerializeNullDataValuesDataProvider()
    {
        return [
            'should serialize null values' => [true, true],
            'should not serialize null values' => [false, false],
        ];
    }

    /**
     * @dataProvider createResponseDataProvider
     */
    public function testCreateResponse($expected, $formats)
    {
        $viewHandler = $this->createViewHandler($formats);
        $viewHandler->registerHandler('html', function ($handler, $view) {
            return $view;
        });

        $response = $viewHandler->handle(new View(null, $expected), new Request());
        $this->assertEquals($expected, $response->getStatusCode());
    }

    public static function createResponseDataProvider()
    {
        return [
            'no handler' => [Response::HTTP_UNSUPPORTED_MEDIA_TYPE, []],
            'custom handler' => [200, []],
            'transform called' => [200, ['json' => false]],
        ];
    }

    public function testHandleCustom()
    {
        $viewHandler = $this->createViewHandler([]);
        $viewHandler->registerHandler('html', ($callback = function () {
            return 'foo';
        }));

        $this->requestStack->push(new Request());

        $data = ['foo' => 'bar'];

        $view = new View($data);
        $this->assertEquals('foo', $viewHandler->handle($view));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testHandleNotSupported()
    {
        $viewHandler = $this->createViewHandler([]);

        $this->requestStack->push(new Request());

        $data = ['foo' => 'bar'];

        $view = new View($data);
        $viewHandler->handle($view);
    }

    public function testConfigurableViewHandlerInterface()
    {
        //test
        $viewHandler = $this->createViewHandler();
        $viewHandler->setExclusionStrategyGroups('bar');
        $viewHandler->setExclusionStrategyVersion('1.1');
        $viewHandler->setSerializeNullStrategy(true);

        $contextMethod = new \ReflectionMethod($viewHandler, 'getSerializationContext');
        $contextMethod->setAccessible(true);

        $view = new View();
        $context = $contextMethod->invoke($viewHandler, $view);
        $this->assertEquals(['bar'], $context->getGroups());
        $this->assertEquals('1.1', $context->getVersion());
        $this->assertTrue($context->getSerializeNull());
    }

    /**
     * @dataProvider exceptionWrapperSerializeResponseContentProvider
     *
     * @param string $format
     */
    public function testCreateResponseWithFormErrorsAndSerializationGroups($format)
    {
        $form = Forms::createFormFactory()->createBuilder()
            ->add('name', TextType::class)
            ->add('description', TextType::class)
            ->getForm();

        $form->get('name')->addError(new FormError('Invalid name'));

        $exceptionWrapper = [
            'status_code' => 400,
            'message' => 'Validation Failed',
            'errors' => $form,
        ];

        $view = new View($exceptionWrapper);
        $view->getContext()->addGroups(array('Custom'));

        $translatorMock = $this->getMockBuilder('Symfony\Component\Translation\TranslatorInterface')
            ->setMethods(array('trans', 'transChoice', 'setLocale', 'getLocale'))
            ->getMock();
        $translatorMock
            ->expects($this->any())
            ->method('trans')
            ->will($this->returnArgument(0));

        $viewHandler = $this->createViewHandler([]);
        $response = $viewHandler->createResponse($view, new Request(), $format);

        $viewHandler = $this->createViewHandler([]);
        $view2 = new View($exceptionWrapper);
        $response2 = $viewHandler->createResponse($view2, new Request(), $format);

        $this->assertEquals($response->getContent(), $response2->getContent());
    }

    /**
     * @return array
     */
    public function exceptionWrapperSerializeResponseContentProvider()
    {
        return [
            'json' => ['json'],
            'xml' => ['xml'],
        ];
    }

    private function createViewHandler($formats = null, $failedValidationCode = Response::HTTP_BAD_REQUEST, $emptyContentCode = Response::HTTP_NO_CONTENT, $serializeNull = false, $forceRedirects = null, $defaultEngine = 'twig')
    {
        return new ViewHandler(
            $this->router,
            $this->serializer,
            $this->requestStack,
            $formats,
            $failedValidationCode,
            $emptyContentCode,
            $serializeNull,
            $forceRedirects,
            $defaultEngine
        );
    }
}
