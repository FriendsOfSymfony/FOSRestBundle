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

    /**
     * Test setup.
     */
    public function setup()
    {
        $this->controller = [new \stdClass(), 'fooAction'];

        $this->params = [];
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

        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();
    }

    /**
     * @expectedDeprecation Using no validator is deprecated since FOSRestBundle 2.6. The `$validator` constructor argument of the `FOS\RestBundle\Request\ParamFetcher` will become mandatory in 3.0.
     * @group legacy
     */
    public function testConstructorCallWithNullValidatorShouldTriggerDeprecation()
    {
        new ParamFetcher($this->container, $this->paramReader, $this->requestStack, null);
    }

    public function testParamDynamicCreation()
    {
        $this->paramFetcher->setController($this->controller);

        $param1 = $this->createParam('foo');
        $param2 = $this->createParam('foobar');
        $param3 = $this->createParam('bar');
        $this->setParams(array($param1)); // Controller params
        $this->paramFetcher->addParam($param2);
        $this->paramFetcher->addParam($param3);

        $this->assertEquals(array('foo' => $param1, 'foobar' => $param2, 'bar' => $param3), $this->paramFetcher->getParams());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No @ParamInterface configuration for parameter 'foo'.
     */
    public function testInexistentParam()
    {
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

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The ParamFetcher requirements feature requires the symfony/validator component.
     *
     * @group legacy
     */
    public function testEmptyValidator()
    {
        $this->setParams([
            $this->createParamWithConstraints('none'),
        ]);

        $fetcher = new ParamFetcher($this->createMock(ContainerInterface::class), $this->paramReader, $this->requestStack);

        $fetcher->setController($this->controller);
        $fetcher->get('none', '42');
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
            ->willReturn(array());

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

    /**
     * @expectedException \FOS\RestBundle\Exception\InvalidParameterException
     * @expectedMessage expected exception.
     */
    public function testValidationErrorsInStrictMode()
    {
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

        $this->assertEquals(array('foo' => 'first', 'bar' => 'second'), $this->paramFetcher->all());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Controller and method needs to be set via setController
     */
    public function testEmptyControllerExceptionWhenInitParams()
    {
        $this->validator
            ->method('validate')
            ->willReturn(new ConstraintViolationList());

        $this->setParams([
            $this->createParam('foo'),
        ]);
        $this->paramFetcher->all();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Controller needs to be set as a class instance (closures/functions are not supported)
     * @dataProvider invalidControllerProvider
     */
    public function testNotCallableControllerExceptionWhenInitParams($controller)
    {
        $this->paramFetcher->setController($controller);

        $this->paramFetcher->all();
    }

    public function invalidControllerProvider()
    {
        return [
            ['controller'],
            [[null, 'foo']],
            [['Foo', null]],
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No @ParamInterface configuration for parameter 'foobar'.
     */
    public function testInexistentIncompatibleParam()
    {
        $request = $this->requestStack->getCurrentRequest();
        $request->query->set('bar', 'value');

        $this->setParams([
            $this->createParam('fos'),
            $this->createIncompatibleParam('bar', ['foobar', 'fos']),
        ]);

        $this->paramFetcher->setController($this->controller);
        $this->paramFetcher->get('bar');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage 'bar' param is incompatible with fos param.
     */
    public function testIncompatibleParam()
    {
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

    protected function setParams(array $params = array())
    {
        $newParams = array();
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
