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

use Doctrine\Common\Util\ClassUtils;
use FOS\RestBundle\Exception\InvalidParameterException;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Request\ParamReaderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
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
class ParamFetcherTest extends \PHPUnit_Framework_TestCase
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

    /**
     * Test setup.
     */
    public function setup()
    {
        $this->controller = [new \stdClass(), 'fooAction'];

        $this->params = [];
        $this->paramReader = $this->getMockBuilder(ParamReaderInterface::class)->getMock();

        $this->validator = $this->getMockBuilder(ValidatorInterface::class)->getMock();

        $this->request = new Request();
        $this->requestStack = $this->getMockBuilder(RequestStack::class)->getMock();
        $this->requestStack
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($this->request);

        $this->paramFetcherBuilder = $this->getMockBuilder(ParamFetcher::class);
        $this->paramFetcherBuilder
            ->setConstructorArgs(array(
                $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock(),
                $this->paramReader,
                $this->requestStack,
                $this->validator,
            ))
            ->setMethods(null);

        $this->container = $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock();
    }

    public function testParamDynamicCreation()
    {
        $fetcher = $this->paramFetcherBuilder->getMock();
        $fetcher->setController($this->controller);

        $param1 = $this->createMockedParam('foo');
        $param2 = $this->createMockedParam('foobar');
        $param3 = $this->createMockedParam('bar');
        $this->setParams(array($param1)); // Controller params
        $fetcher->addParam($param2);
        $fetcher->addParam($param3);

        $this->assertEquals(array('foo' => $param1, 'foobar' => $param2, 'bar' => $param3), $fetcher->getParams());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage No @ParamInterface configuration for parameter 'foo'.
     */
    public function testInexistentParam()
    {
        $fetcher = $this->paramFetcherBuilder
            ->setMethods(array('getParams'))
            ->getMock();
        $fetcher
            ->expects($this->once())
            ->method('getParams')
            ->willReturn(array(
                'bar' => $this->createMockedParam('bar'),
            ));
        $fetcher->get('foo');
    }

    public function testDefaultReplacement()
    {
        $fetcher = $this->paramFetcherBuilder
            ->setMethods(['getParams', 'cleanParamWithRequirements'])
            ->getMock();

        $param = $this->createMockedParam('foo', 'bar'); // Default value: bar
        $fetcher
            ->expects($this->once())
            ->method('getParams')
            ->willReturn(['foo' => $param]);
        $fetcher
            ->expects($this->once())
            ->method('cleanParamWithRequirements')
            ->with($param, 'bar', true)
            ->willReturn('foooo');

        $this->assertEquals('foooo', $fetcher->get('foo', true));
    }

    public function testReturnBeforeGettingConstraints()
    {
        $param = $this->getMockBuilder(\FOS\RestBundle\Controller\Annotations\ParamInterface::class)->getMock();
        $param
            ->expects($this->never())
            ->method('getConstraints');

        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);

        $this->assertEquals(
            'default',
            $method->invokeArgs($fetcher, array($param, 'default', null, 'default'))
        );
    }

    public function testReturnWhenEmptyConstraints()
    {
        $param = $this->createMockedParam('foo');
        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);

        $this->assertEquals(
            'value',
            $method->invokeArgs($fetcher, array($param, 'value', null, null))
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The ParamFetcher requirements feature requires the symfony/validator component.
     */
    public function testEmptyValidator()
    {
        $param = $this->createMockedParam('none', null, array(), false, null, array('constraint'));
        $this->setParams([$param]);

        list($fetcher, $method) = $this->getFetcherToCheckValidation(
            $param,
            array(
                $this->getMockBuilder('Symfony\Component\DependencyInjection\ContainerInterface')->getMock(),
                $this->paramReader,
                $this->requestStack,
                null,
            )
        );

        $fetcher->setController($this->controller);
        $fetcher->get('none', '42');
    }

    public function testNoValidationErrors()
    {
        $param = $this->createMockedParam('foo', null, array(), false, null, array('constraint'));
        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);
        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with('value', array('constraint'))
            ->willReturn(array());

        $this->assertEquals('value', $method->invokeArgs($fetcher, array($param, 'value', null, null)));
    }

    public function testValidationErrors()
    {
        $param = $this->createMockedParam('foo', 'default', [], false, null, ['constraint']);
        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);

        $errors = $this->getMockBuilder(ConstraintViolationListInterface::class)->getMock();
        $errors
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with('value', ['constraint'])
            ->willReturn($errors);

        $this->assertEquals('default', $method->invokeArgs($fetcher, array($param, 'value', false, 'default')));
    }

    public function testValidationException()
    {
        $param = $this->createMockedParam('foo', 'default', [], true, null, ['constraint']);
        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);

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
            ->with('value', ['constraint'])
            ->willReturn($errors);

        try {
            $method->invokeArgs($fetcher, array($param, 'value', true, 'default'));
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
        $param = $this->createMockedParam('foo', null, [], false, null, ['constraint']);
        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);

        $errors = $this->getMockBuilder(ConstraintViolationListInterface::class)->getMock();
        $errors
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $this->validator
            ->expects($this->once())
            ->method('validate')
            ->with('value', array('constraint'))
            ->willReturn($errors);

        $method->invokeArgs($fetcher, array($param, 'value', true, null));
    }

    protected function getFetcherToCheckValidation($param, array $constructionArguments = null)
    {
        $this->paramFetcherBuilder->setMethods(array('checkNotIncompatibleParams'));

        if (null !== $constructionArguments) {
            $this->paramFetcherBuilder->setConstructorArgs($constructionArguments);
        }

        $fetcher = $this->paramFetcherBuilder->getMock();

        $fetcher
            ->expects($this->once())
            ->method('checkNotIncompatibleParams')
            ->with($param);

        $reflection = new \ReflectionClass($fetcher);
        $method = $reflection->getMethod('cleanParamWithRequirements');
        $method->setAccessible(true);

        return [$fetcher, $method];
    }

    public function testAllGetter()
    {
        $fetcher = $this->paramFetcherBuilder
            ->setMethods(array('getParams', 'get'))
            ->getMock();

        $fetcher
            ->expects($this->once())
            ->method('getParams')
            ->willReturn(array(
                'foo' => $this->createMockedParam('foo', null, array(), true), // strict
                'bar' => $this->createMockedParam('bar'),
            ));

        $fetcher
            ->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->logicalOr('foo', 'bar'),
                $this->logicalOr(true, false)
            )
            ->will($this->onConsecutiveCalls('first', 'second'));

        $this->assertEquals(array('foo' => 'first', 'bar' => 'second'), $fetcher->all());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Controller and method needs to be set via setController
     */
    public function testEmptyControllerExceptionWhenInitParams()
    {
        $fetcher = $this->paramFetcherBuilder->getMock();
        $fetcher->all();
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Controller needs to be set as a class instance (closures/functions are not supported)
     * @dataProvider invalidControllerProvider
     */
    public function testNotCallableControllerExceptionWhenInitParams($controller)
    {
        $fetcher = $this->paramFetcherBuilder->getMock();
        $fetcher->setController($controller);

        $fetcher->all();
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
        $fetcher = $this->paramFetcherBuilder
            ->setMethods(array('getParams'))
            ->getMock();
        $fetcher
            ->expects($this->once())
            ->method('getParams')
            ->willReturn(array('foo' => $this->createMockedParam('foo')));

        // Incompatible with foobar & fos when bar value not null
        $param = $this->createMockedParam('bar', null, array('foobar', 'fos'), false, 'value');

        $reflection = new \ReflectionClass($fetcher);
        $method = $reflection->getMethod('checkNotIncompatibleParams');
        $method->setAccessible(true);

        $method->invokeArgs($fetcher, array($param));
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage 'bar' param is incompatible with fos param.
     */
    public function testIncompatibleParam()
    {
        $fetcher = $this->paramFetcherBuilder
            ->setMethods(array('getParams'))
            ->getMock();
        $fetcher
            ->expects($this->once())
            ->method('getParams')
            ->willReturn(array(
                'foobar' => $this->createMockedParam('foobar'),
                'fos' => $this->createMockedParam('fos', null, array(), false, 'value'),
            ));

        // Incompatible with foobar & fos when bar value not null
        $param = $this->createMockedParam('bar', null, array('foobar', 'fos'), false, 'value');

        $reflection = new \ReflectionClass($fetcher);
        $method = $reflection->getMethod('checkNotIncompatibleParams');
        $method->setAccessible(true);

        $method->invokeArgs($fetcher, array($param));
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
            ->with(new \ReflectionClass(ClassUtils::getClass($this->controller[0])), $this->controller[1])
            ->willReturn($newParams);
    }

    protected function createMockedParam(
        $name,
        $default = null,
        array $incompatibles = [],
        $strict = false,
        $value = null,
        array $constraints = []
    ) {
        $param = $this->getMockBuilder('FOS\RestBundle\Controller\Annotations\ParamInterface')->getMock();
        $param
            ->expects($this->any())
            ->method('getName')
            ->willReturn($name);
        $param
            ->expects($this->any())
            ->method('getDefault')
            ->willReturn($default);
        $param
            ->expects($this->any())
            ->method('getIncompatibilities')
            ->willReturn($incompatibles);
        $param
            ->expects($this->any())
            ->method('getConstraints')
            ->willReturn($constraints);
        $param
            ->expects($this->any())
            ->method('isStrict')
            ->willReturn($strict);
        $param
            ->expects($this->any())
            ->method('getValue')
            ->with($this->request, $default)
            ->will($value !== null ? $this->returnValue($value) : $this->returnArgument(1));

        return $param;
    }
}
