<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Request;

use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Exception\InvalidParameterException;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Request\ParamReaderInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * ParamFetcher test.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Boris Guéry <guery.b@gmail.com>
 */
class ParamFetcherTest extends TestCase
{
    /**
     * @var callable
     */
    private $controller;

    /**
     * @var ParamReaderInterface
     */
    private $paramReader;

    /**
     * @var ParamFetcherTest|ValidatorInterface
     */
    private $validator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    private $paramFetcher;

    protected function setUp(): void
    {
        $this->controller = [new TestController(), 'getAction'];

        $this->paramReader = $this->getMockBuilder(ParamReaderInterface::class)->getMock();

        $this->validator = $this->getMockBuilder(ValidatorInterface::class)->getMock();

        $this->requestStack = new RequestStack();
        $this->requestStack->push(new Request());

        $this->paramFetcher = new ParamFetcher(
            $this->createMock(ContainerInterface::class),
            $this->paramReader,
            $this->requestStack,
            $this->validator
        );
    }

    public function testParamDynamicCreation()
    {
        $this->paramFetcher->setController($this->controller);

        $param1 = $this->createParam('foo');
        $param2 = $this->createParam('foobar');
        $param3 = $this->createParam('bar');
        $this->setParams([$param1]); // Controller params
        $this->paramFetcher->addParam($param2);
        $this->paramFetcher->addParam($param3);

        $this->assertEquals(['foo' => $param1, 'foobar' => $param2, 'bar' => $param3], $this->paramFetcher->getParams());
    }

    public function testInexistentParam()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No @ParamInterface configuration for parameter \'foo\'.');

        $this->paramFetcher->setController($this->controller);
        $this->setParams([$this->createParam('bar')]);
        $this->paramFetcher->get('foo');
    }

    public function testDefaultReplacement()
    {
        $request = $this->requestStack->getCurrentRequest();
        $request->query->set('foo', 'foooo');

        $this->setParams([
            $this->createParam('foo', 'bar'), // Default value: bar
        ]);

        $this->paramFetcher->setController($this->controller);

        $this->assertEquals('foooo', $this->paramFetcher->get('foo', true));
    }

    public function testReturnBeforeGettingConstraints()
    {
        $this->setParams([
            $this->createParamWithConstraints('foo', 'default'),
        ]);

        $this->validator
            ->expects($this->never())
            ->method('validate');

        $this->paramFetcher->setController($this->controller);
        $this->assertSame('default', $this->paramFetcher->get('foo'));
    }

    public function testReturnWhenEmptyConstraints()
    {
        $this->setParams([
            $this->createParam('foo'),
        ]);

        $request = $this->requestStack->getCurrentRequest();
        $request->query->set('foo', 'value');

        $this->paramFetcher->setController($this->controller);

        $this->assertSame('value', $this->paramFetcher->get('foo'));
    }

    public function testNoValidationErrors()
    {
        $request = $this->requestStack->getCurrentRequest();
        $request->query->set('foo', 'value');

        $this->setParams([
            $this->createParamWithConstraints('foo'),
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with('value', [new NotBlank()])
            ->willReturn($this->getMockBuilder(ConstraintViolationListInterface::class)->getMock());

        $this->paramFetcher->setController($this->controller);

        $this->assertSame('value', $this->paramFetcher->get('foo'));
    }

    public function testValidationErrors()
    {
        $request = $this->requestStack->getCurrentRequest();
        $request->query->set('foo', 'value');

        $this->setParams([
            $this->createParamWithConstraints('foo', 'default'),
        ]);

        $errors = $this->getMockBuilder(ConstraintViolationListInterface::class)->getMock();
        $errors
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with('value', [new NotBlank()])
            ->willReturn($errors);

        $this->paramFetcher->setController($this->controller);

        $this->assertSame('default', $this->paramFetcher->get('foo'));
    }

    public function testValidationException()
    {
        $request = $this->requestStack->getCurrentRequest();
        $request->query->set('foo', 'value');

        $param = $this->createParamWithConstraints('foo', 'default');
        $param->strict = true;

        $this->setParams([$param]);

        $stringInvalidValue = '12345';
        $stringViolation = $this->getMockBuilder(ConstraintViolationInterface::class)
            ->getMock();
        $stringViolation->method('getInvalidValue')
            ->willReturn($stringInvalidValue);

        $arrayInvalidValue = ['page' => 'abcde'];
        $arrayViolation = $this->getMockBuilder(ConstraintViolationInterface::class)
            ->getMock();
        $arrayViolation->method('getInvalidValue')
            ->willReturn($arrayInvalidValue);

        $errors = new ConstraintViolationList([
            $stringViolation,
            $arrayViolation,
        ]);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with('value', [new NotBlank()])
            ->willReturn($errors);

        try {
            $this->paramFetcher->setController($this->controller);
            $this->paramFetcher->get('foo');
            $this->fail(sprintf('An exception must be thrown in %s', __METHOD__));
        } catch (InvalidParameterException $exception) {
            $this->assertSame($param, $exception->getParameter());
            $this->assertSame($errors, $exception->getViolations());
            $this->assertEquals(
                sprintf('Parameter "foo" of value "%s" violated a constraint ""', $stringInvalidValue).
                sprintf(
                    "\n".'Parameter "foo" of value "%s" violated a constraint ""',
                    var_export($arrayInvalidValue, true)
                ),
                $exception->getMessage()
            );
        }
    }

    public function testValidationErrorsInStrictMode()
    {
        $this->expectException(InvalidParameterException::class);

        $request = $this->requestStack->getCurrentRequest();
        $request->query->set('foo', 'value');

        $param = $this->createParamWithConstraints('foo', 'default');
        $param->strict = true;

        $this->setParams([$param]);

        $errors = $this->getMockBuilder(ConstraintViolationListInterface::class)->getMock();
        $errors
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with('value', [new NotBlank()])
            ->willReturn($errors);

        $this->paramFetcher->setController($this->controller);
        $this->paramFetcher->get('foo');
    }

    public function testAllGetter()
    {
        $request = $this->requestStack->getCurrentRequest();
        $request->query->set('foo', 'first');
        $request->query->set('bar', 'second');

        $this->setParams([
            $this->createStrictParam('foo'), // strict
            $this->createParam('bar'),
        ]);

        $this->paramFetcher->setController($this->controller);

        $this->assertEquals(['foo' => 'first', 'bar' => 'second'], $this->paramFetcher->all());
    }

    public function testEmptyControllerExceptionWhenInitParams()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Controller and method needs to be set via setController');

        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->setParams([
            $this->createParam('foo'),
        ]);
        $this->paramFetcher->all();
    }

    /**
     * @dataProvider invalidControllerProvider
     */
    public function testNotCallableControllerExceptionWhenInitParams($controller)
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Controller needs to be set as a class instance (closures/functions are not supported)');

        $this->paramFetcher->setController($controller);

        $this->paramFetcher->all();
    }

    public function invalidControllerProvider()
    {
        return [
            ['strtolower'],
            [[self::class, 'controllerAction']],
            [function () {}],
        ];
    }

    public function testInexistentIncompatibleParam()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No @ParamInterface configuration for parameter \'foobar\'.');

        $request = $this->requestStack->getCurrentRequest();
        $request->query->set('bar', 'value');

        $this->setParams([
            $this->createParam('fos'),
            $this->createIncompatibleParam('bar', ['foobar', 'fos']),
        ]);

        $this->paramFetcher->setController($this->controller);
        $this->paramFetcher->get('bar');
    }

    public function testIncompatibleParam()
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('"bar" param is incompatible with fos param.');

        $request = $this->requestStack->getCurrentRequest();
        $request->query->set('fos', 'value');
        $request->query->set('bar', 'value');

        $this->setParams([
            $this->createParam('foobar'),
            $this->createParam('fos'),
            $this->createIncompatibleParam('bar', ['foobar', 'fos']),
        ]);

        $this->paramFetcher->setController($this->controller);
        $this->paramFetcher->get('bar');
    }

    public static function controllerAction()
    {
    }

    protected function setParams(array $params = [])
    {
        $newParams = [];
        foreach ($params as $param) {
            $newParams[$param->getName()] = $param;
        }

        $this->paramReader
            ->expects($this->any())
            ->method('read')
            ->with(new \ReflectionClass(get_class($this->controller[0])), $this->controller[1])
            ->willReturn($newParams);
    }

    private function createParam(string $name, ?string $default = null): QueryParam
    {
        $param = new QueryParam();
        $param->nullable = true;
        $param->name = $name;
        $param->key = $name;
        $param->default = $default;

        return $param;
    }

    private function createStrictParam(string $name, ?string $default = null): QueryParam
    {
        $param = $this->createParam($name, $default);
        $param->strict = true;

        return $param;
    }

    private function createIncompatibleParam(string $name, array $incompatibles): QueryParam
    {
        $param = $this->createParam($name);
        $param->incompatibles = $incompatibles;

        return $param;
    }

    private function createParamWithConstraints(string $name, ?string $default = null): QueryParam
    {
        $param = $this->createParam($name, $default);
        $param->requirements = new NotBlank();

        return $param;
    }
}

class TestController
{
    public function getAction()
    {
    }
}
