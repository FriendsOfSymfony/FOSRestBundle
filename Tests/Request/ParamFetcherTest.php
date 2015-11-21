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

use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Util\ClassUtils;

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
     * @var \FOS\RestBundle\Request\ParamReaderInterface
     */
    private $paramReader;
    /**
     * @var ParamFetcherTest|\Symfony\Component\Validator\ValidatorInterface
     */
    private $validator;
    /**
     * @var string
     */
    private $validatorMethod;
    /**
     * @var \FOS\RestBundle\Util\ViolationFormatterInterface
     */
    private $violationFormatter;
    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    private $request;

    /**
     * Test setup.
     */
    public function setup()
    {
        $this->controller = array(new \stdClass(), 'fooAction');

        $this->params = array();
        $this->paramReader = $this->getMock('FOS\RestBundle\Request\ParamReaderInterface');

        if (interface_exists('Symfony\Component\Validator\Validator\ValidatorInterface')) {
            $this->validator = $this->getMock('Symfony\Component\Validator\Validator\ValidatorInterface');
            $this->validatorMethod = 'validate';
        } else {
            $this->validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
            $this->validatorMethod = 'validateValue';
        }

        $this->violationFormatter = $this->getMock('FOS\RestBundle\Validator\ViolationFormatterInterface');

        $this->request = new Request();

        $this->paramFetcherBuilder = $this->getMockBuilder('FOS\RestBundle\Request\ParamFetcher');
        $this->paramFetcherBuilder
            ->setConstructorArgs(array(
                $this->paramReader,
                $this->request,
                $this->violationFormatter,
                $this->validator,
            ))
            ->setMethods(null);
    }

    public function testControllerSetter()
    {
        $fetcher = $this->paramFetcherBuilder->getMock();
        $fetcher->setController($this->controller);
        $this->assertEquals($this->controller, \PHPUnit_Framework_Assert::readAttribute($fetcher, 'controller'));
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
            ->setMethods(array('getParams', 'cleanParamWithRequirements'))
            ->getMock();

        $param = $this->createMockedParam('foo', 'bar'); // Default value: bar
        $fetcher
            ->expects($this->once())
            ->method('getParams')
            ->willReturn(array('foo' => $param));
        $fetcher
            ->expects($this->once())
            ->method('cleanParamWithRequirements')
            ->with($param, 'bar', true)
            ->willReturn('foooo');

        $this->assertEquals('foooo', $fetcher->get('foo', true));
    }

    public function testReturnBeforeGettingConstraints()
    {
        $param = $this->getMock('FOS\RestBundle\Controller\Annotations\ParamInterface');
        $param
            ->expects($this->once())
            ->method('getDefault')
            ->willReturn('default');
        $param
            ->expects($this->never())
            ->method('getConstraints');

        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);

        $this->assertEquals(
            'default',
            $method->invokeArgs($fetcher, array($param, 'default', null))
        );
    }

    public function testReturnWhenEmptyConstraints()
    {
        $param = $this->createMockedParam('foo');
        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);

        $this->assertEquals(
            'value',
            $method->invokeArgs($fetcher, array($param, 'value', null))
        );
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage The ParamFetcher requirements feature requires the symfony/validator component.
     */
    public function testEmptyValidator()
    {
        $param = $this->createMockedParam('foo', null, array(), false, null, array('constraint'));
        list($fetcher, $method) = $this->getFetcherToCheckValidation(
            $param,
            array(
                $this->paramReader,
                $this->request,
                $this->violationFormatter,
                null,
            )
        );

        $method->invokeArgs($fetcher, array($param, 'value', null));
    }

    public function testNoValidationErrors()
    {
        $param = $this->createMockedParam('foo', null, array(), false, null, array('constraint'));
        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);
        $this->validator
            ->expects($this->once())
            ->method($this->validatorMethod)
            ->with('value', array('constraint'))
            ->willReturn(array());

        $this->assertEquals('value', $method->invokeArgs($fetcher, array($param, 'value', null)));
    }

    public function testValidationErrors()
    {
        $param = $this->createMockedParam('foo', 'default', array(), false, null, array('constraint'));
        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);

        $errors = $this->getMock('Symfony\Component\Validator\ConstraintViolationListInterface');
        $errors
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $this->validator
            ->expects($this->once())
            ->method($this->validatorMethod)
            ->with('value', array('constraint'))
            ->willReturn($errors);

        $this->assertEquals('default', $method->invokeArgs($fetcher, array($param, 'value', false)));
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedMessage expected exception.
     */
    public function testValidationErrorsInStrictMode()
    {
        $param = $this->createMockedParam('foo', null, array(), false, null, array('constraint'));
        list($fetcher, $method) = $this->getFetcherToCheckValidation($param);

        $errors = $this->getMock('Symfony\Component\Validator\ConstraintViolationListInterface');
        $errors
            ->expects($this->once())
            ->method('count')
            ->willReturn(1);

        $this->validator
            ->expects($this->once())
            ->method($this->validatorMethod)
            ->with('value', array('constraint'))
            ->willReturn($errors);
        $this->violationFormatter
            ->expects($this->once())
            ->method('formatList')
            ->with($param, $errors)
            ->willReturn('expected exception.');

        $method->invokeArgs($fetcher, array($param, 'value', true));
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

        return array($fetcher, $method);
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

        $reflection = new \ReflectionClass($fetcher);
        $method = $reflection->getMethod('initParams');
        $method->setAccessible(true);

        $method->invokeArgs($fetcher, array());
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

        $reflection = new \ReflectionClass($fetcher);
        $method = $reflection->getMethod('initParams');
        $method->setAccessible(true);

        $method->invokeArgs($fetcher, array());
    }

    public function invalidControllerProvider()
    {
        return array(
            array('controller'),
            array(array(null, 'foo')),
            array(array('Foo', null)),
        );
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

        $param = $this->createMockedParam('bar', null, array('foobar', 'fos')); // Incompatible with foobar & fos

        $reflection = new \ReflectionClass($fetcher);
        $method = $reflection->getMethod('checkNotIncompatibleParams');
        $method->setAccessible(true);

        $method->invokeArgs($fetcher, array($param));
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
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

        $param = $this->createMockedParam('bar', null, array('foobar', 'fos')); // Incompatible with foobar & fos

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
        array $incompatibles = array(),
        $strict = false,
        $value = null,
        array $constraints = array()
    ) {
        $param = $this->getMock('FOS\RestBundle\Controller\Annotations\ParamInterface');
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
