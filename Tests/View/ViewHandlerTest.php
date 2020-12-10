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

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Serializer\Serializer;
use FOS\RestBundle\View\View;
use FOS\RestBundle\View\ViewHandler;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Translation\TranslatorInterface;

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

    protected function setUp(): void
    {
        $this->router = $this->getMockBuilder(RouterInterface::class)->getMock();
        $this->serializer = $this->getMockBuilder(Serializer::class)->getMock();
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

    /**
     * @dataProvider getStatusCodeDataProvider
     */
    public function testGetStatusCode(
        $expected,
        $data,
        $isSubmitted,
        $isValid,
        $noContentCode,
        $actualStatusCode = null
    ) {
        $form = $this->createMock(FormInterface::class);
        $form
            ->expects($this->any())
            ->method('isSubmitted')
            ->will($this->returnValue($isSubmitted));
        $form
            ->expects($this->any())
            ->method('isValid')
            ->will($this->returnValue($isValid));

        if ($data) {
            $data = ['form' => $form];
        }
        $view = new View($data ?: null, $actualStatusCode ?: null);

        if ($data) {
            $expectedContext = new Context();

            if (null !== $actualStatusCode || $form->isSubmitted() && !$form->isValid()) {
                $expectedContext->setAttribute('status_code', $expected);
            }

            $this->serializer
                ->expects($this->once())
                ->method('serialize')
                ->with($form, 'json', $expectedContext)
                ->willReturn(json_encode(['code' => $expected, 'message' => $isValid ? 'OK' : 'Validation failed']));
        }

        $viewHandler = $this->createViewHandler([], $expected, $noContentCode);
        $this->assertEquals($expected, $viewHandler->createResponse($view, new Request(), 'json')->getStatusCode());
    }

    public static function getStatusCodeDataProvider()
    {
        return [
            'no data' => [Response::HTTP_OK, false, false, false, Response::HTTP_OK],
            'no data with 204' => [Response::HTTP_NO_CONTENT, false, false, false, Response::HTTP_NO_CONTENT],
            'no data, but custom response code' => [Response::HTTP_OK, false, false, false, Response::HTTP_NO_CONTENT, Response::HTTP_OK],
            'form key form not bound' => [Response::HTTP_OK, true, false, true, Response::HTTP_OK],
            'form key form is bound and invalid' => [Response::HTTP_FORBIDDEN, true, true, false, Response::HTTP_OK],
            'form key form is bound and invalid and status code in view is set' => [Response::HTTP_FORBIDDEN, true, true, false, Response::HTTP_OK, Response::HTTP_CREATED],
            'form key form bound and valid' => [Response::HTTP_OK, true, true, true, Response::HTTP_OK],
            'form key null form bound and valid' => [Response::HTTP_OK, true, true, true, Response::HTTP_OK],
        ];
    }

    public function testCreateResponseWithLocation()
    {
        $viewHandler = $this->createViewHandler(['html' => false, 'json' => false, 'xml' => false], Response::HTTP_BAD_REQUEST, Response::HTTP_OK, false);
        $view = new View();
        $view->setLocation('foo');
        $returnedResponse = $viewHandler->createResponse($view, new Request(), 'json');

        $this->assertEquals(Response::HTTP_OK, $returnedResponse->getStatusCode());
        $this->assertEquals('foo', $returnedResponse->headers->get('location'));
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
                return serialize([$data, $context]);
            }));

        //test
        $viewHandler = $this->createViewHandler(null, $expectedFailedValidationCode = Response::HTTP_I_AM_A_TEAPOT);

        $form = $this->getMockBuilder('Symfony\\Component\\Form\\Form')
            ->disableOriginalConstructor()
            ->setMethods(['createView', 'getData', 'isValid', 'isSubmitted'])
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
            $data = $this->getMockBuilder(Form::class)
                ->disableOriginalConstructor()
                ->setMethods(['createView', 'getData', 'isValid', 'isSubmitted'])
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
            'no form' => ['json', ['foo' => 'bar']],
            'invalid form' => ['json', ['data' => [0 => 'error', 1 => 'error']], 0, false, true],
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
        $viewHandler->registerHandler('html', function ($handler, View $view) {
            return new Response('', $view->getStatusCode());
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
        $viewHandler->registerHandler('html', (function () {
            return new Response('foo');
        }));

        $this->requestStack->push(new Request());

        $data = ['foo' => 'bar'];

        $view = new View($data);
        $this->assertEquals('foo', $viewHandler->handle($view)->getContent());
    }

    public function testHandleNotSupported()
    {
        $this->expectException(HttpException::class);

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
        $view->getContext()->addGroups(['Custom']);

        $translatorMock = $this->getMockBuilder(TranslatorInterface::class)
            ->setMethods(['trans', 'transChoice', 'setLocale', 'getLocale'])
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

    private function createViewHandler($formats = null, $failedValidationCode = Response::HTTP_BAD_REQUEST, $emptyContentCode = Response::HTTP_NO_CONTENT, $serializeNull = false)
    {
        return ViewHandler::create(
            $this->router,
            $this->serializer,
            $this->requestStack,
            $formats,
            $failedValidationCode,
            $emptyContentCode,
            $serializeNull
        );
    }
}
