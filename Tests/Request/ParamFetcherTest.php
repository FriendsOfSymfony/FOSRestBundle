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

use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * ParamFetcher test.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Boris Guéry <guery.b@gmail.com>
 */
class ParamFetcherTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    private $controller;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $paramReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $validator;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $violationFormatter;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $constraint;

    /**
     * Test setup.
     */
    public function setup()
    {
        $this->controller = array(new \stdClass(), 'indexAction');

        $this->paramReader = $this->getMockBuilder('FOS\RestBundle\Request\ParamReader')
            ->disableOriginalConstructor()
            ->getMock();

        $this->constraint = $this->getMockForAbstractClass('Symfony\Component\Validator\Constraint');

        $annotations = array();
        $annotations['foo'] = new QueryParam();
        $annotations['foo']->name = 'foo';
        $annotations['foo']->requirements = '\d+';
        $annotations['foo']->default = '1';
        $annotations['foo']->description = 'The foo';
        $annotations['foo']->nullable = false;

        $annotations['bar'] = new RequestParam();
        $annotations['bar']->name = 'bar';
        $annotations['bar']->requirements = '\d+';
        $annotations['bar']->description = 'The bar';

        $annotations['baz'] = new RequestParam();
        $annotations['baz']->name = 'baz';
        $annotations['baz']->requirements = '\d?';

        $annotations['buzz'] = new QueryParam();
        $annotations['buzz']->array = true;
        $annotations['buzz']->name = 'buzz';
        $annotations['buzz']->requirements = '\d+';
        $annotations['buzz']->default = '1';
        $annotations['buzz']->nullable = false;
        $annotations['buzz']->description = 'An array';

        $annotations['boo'] = new QueryParam();
        $annotations['boo']->array = true;
        $annotations['boo']->name = 'boo';
        $annotations['boo']->description = 'An array with no default value';

        $annotations['boozz'] = new QueryParam();
        $annotations['boozz']->name = 'boozz';
        $annotations['boozz']->requirements = '\d+';
        $annotations['boozz']->description = 'A scalar param with no default value (an optional limit param for example)';

        $annotations['biz'] = new QueryParam();
        $annotations['biz']->name = 'biz';
        $annotations['biz']->key = 'business';
        $annotations['biz']->requirements = '\d+';
        $annotations['biz']->default = null;
        $annotations['biz']->nullable = true;
        $annotations['biz']->description = 'A scalar param with an explicitly defined null default';

        $annotations['arr'] = new RequestParam();
        $annotations['arr']->name = 'arr';
        $annotations['arr']->array = true;

        $annotations['arr_null_strict'] = new RequestParam();
        $annotations['arr_null_strict']->name = 'arr_null_strict';
        $annotations['arr_null_strict']->array = true;
        $annotations['arr_null_strict']->nullable = true;
        $annotations['arr_null_strict']->strict = true;

        $annotations['moo'] = new QueryParam();
        $annotations['moo']->name = 'mooh';
        $annotations['moo']->key = 'moo';
        $annotations['moo']->requirements = '\d+';
        $annotations['moo']->default = null;
        $annotations['moo']->nullable = true;
        $annotations['moo']->allowBlank = false;
        $annotations['moo']->description = 'A scalar param with an explicitly defined null default';

        $this->paramReader
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValue($annotations));

        $this->validator = $this->getMock('Symfony\Component\Validator\ValidatorInterface');
        $this->violationFormatter = $this->getMock('FOS\RestBundle\Util\ViolationFormatterInterface');
    }

    /**
     * Get a param fetcher.
     *
     * @param array $query      Query parameters for the request.
     * @param array $request    Request parameters for the request.
     * @param array $attributes Attributes for the request.
     *
     * @return ParamFetcher
     */
    public function getParamFetcher($query = array(), $request = array(), $attributes = null)
    {
        $attributes = $attributes ?: array('_controller' => __CLASS__.'::stubAction');

        $req = new Request($query, $request, $attributes);

        return new ParamFetcher($this->paramReader, $req, $this->violationFormatter, $this->validator);
    }

    /**
     * Test valid parameters.
     *
     * @param string   $param       which param to test
     * @param string   $expected    Expected query parameter value.
     * @param string   $expectedAll Expected query parameter values.
     * @param array    $query       Query parameters for the request.
     * @param array    $request     Request parameters for the request.
     * @param \Closure $callback    Callback to be applied on the validator
     *
     * @dataProvider validatesConfiguredParamDataProvider
     */
    public function testValidatesConfiguredParam($param, $expected, $expectedAll, $query, $request, \Closure $callback = null)
    {
        if (null !== $callback) {
            $self = $this;
            $validator = $this->validator;
            $callback($validator, $self);
        }

        $queryFetcher = $this->getParamFetcher($query, $request);
        $queryFetcher->setController($this->controller);

        $this->assertEquals($expected, $queryFetcher->get($param));
        $this->assertEquals($expectedAll, $queryFetcher->all());
    }

    /**
     * Data provider for the valid parameters test.
     *
     * @return array Data
     */
    public static function validatesConfiguredParamDataProvider()
    {
        return array(
            array( // check that non-strict missing params take default value
                'foo',
                '1',
                array('foo' => '1', 'bar' => '2', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(),  'moo' => null),
                array(),
                array('bar' => '2', 'baz' => '4', 'arr' => array()),
            ),
            array( // pass Param in GET
                'foo',
                '42',
                array('foo' => '42', 'bar' => '2', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('foo' => '42'),
                array('bar' => '2', 'baz' => '4', 'arr' => array()),
            ),
            array( // check that invalid non-strict params take default value
                'foo',
                '1',
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('foo' => 'bar'),
                array('bar' => '1', 'baz' => '4', 'arr' => array()),
                function (\PHPUnit_Framework_MockObject_MockObject $validator, \PHPUnit_Framework_TestCase $self) {
                    $errors = new ConstraintViolationList(array(
                        new ConstraintViolation("expected error", null, array(), null, null, null),
                    ));

                    $validator->expects($self->at(0))
                        ->method('validateValue')
                        ->with('bar', new Regex(array('pattern' => '#^\\d+$#xsu', 'message' => "Query parameter value 'bar', does not match requirements '\\d+'")), null)
                        ->will($self->returnValue($errors));
                    $validator->expects($self->at(1))
                        ->method('validateValue')
                        ->with('bar', new Regex(array('pattern' => '#^\\d+$#xsu', 'message' => "Query parameter value 'bar', does not match requirements '\\d+'")), null)
                        ->will($self->returnValue($errors));

                },
            ),
            array( // nullable array with strict
                'arr_null_strict',
                array(),
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array(),
                array('bar' => '1', 'baz' => '4', 'arr' => array()),
            ),
            array( // invalid array
                'buzz',
                array(1),
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('buzz' => 'invaliddata'),
                array('bar' => '1', 'baz' => '4', 'arr' => array()),
            ),
            array( // invalid array (multiple depth)
                'buzz',
                array(1),
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('buzz' => array(array(1))),
                array('bar' => '1', 'baz' => '4', 'arr' => array()),
            ),

            array( // multiple array
                'buzz',
                array(2, 3, 4),
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array(), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('buzz' => array(2, 3, 4)),
                array('bar' => '1', 'baz' => '4', 'arr' => array()),
            ),
            array( // multiple array with one invalid value
                'buzz',
                array(2, 1, 4),
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(2, 1, 4), 'boo' => array(), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('buzz' => array(2, 'invaliddata', 4)),
                array('bar' => '1', 'baz' => '4', 'arr' => array()),
                function (\PHPUnit_Framework_MockObject_MockObject $validator, \PHPUnit_Framework_TestCase $self) {
                    $errors = new ConstraintViolationList(array(
                        new ConstraintViolation("expected error", null, array(), null, null, null),
                    ));

                    $validator->expects($self->at(1))
                        ->method('validateValue')
                        ->with('invaliddata', new Regex(array('pattern' => '#^\\d+$#xsu', 'message' => "Query parameter value 'invaliddata', does not match requirements '\\d+'")), null)
                        ->will($self->returnValue($errors));

                    $validator->expects($self->at(6))
                        ->method('validateValue')
                        ->with('invaliddata', new Regex(array('pattern' => '#^\\d+$#xsu', 'message' => "Query parameter value 'invaliddata', does not match requirements '\\d+'")), null)
                        ->will($self->returnValue($errors));
                },
            ),
            array(  // Array not provided in GET query
                'boo',
                array(),
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array(), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('buzz' => array(2, 3, 4)),
                array('bar' => '1', 'baz' => '4', 'arr' => array()),
            ),
            array(  // QueryParam provided in GET query but as a scalar
                'boo',
                array(),
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array(), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('buzz' => array(2, 3, 4), 'boo' => 'scalar'),
                array('bar' => '1', 'baz' => '4', 'arr' => array()),
            ),
            array(  // QueryParam provided in GET query with valid values
                'boo',
                array('1', 'foo', 5),
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5)),
                array('bar' => '1', 'baz' => '4', 'arr' => array()),
            ),
            array(  // QueryParam provided in GET query with valid values
                'boozz',
                null,
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5), 'boozz' => null, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5)),
                array('bar' => '1', 'baz' => '4', 'arr' => array()),
            ),
            array(  // QueryParam provided in GET query with valid values
                'boozz',
                5,
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5), 'boozz' => 5, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => null),
                array('buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5), 'boozz' => 5),
                array('bar' => '1', 'baz' => '4', 'boozz' => 5, 'arr' => array()),
            ),
            array(  // QueryParam provided in GET query with valid values
                'moo',
                'string',
                array('foo' => '1', 'bar' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5), 'boozz' => 5, 'biz' => null, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => 'string'),
                array('buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5), 'boozz' => 5, 'moo' => 'string'),
                array('bar' => '1', 'baz' => '4', 'boozz' => 5, 'arr' => array()),
            )
        );
    }

    public function testValidatesAddParam()
    {
        $queryFetcher = $this->getParamFetcher(array(), array('bar' => '2', 'baz' => '4','bub' => '10', 'arr' => array()));
        $queryFetcher->setController($this->controller);

        $runtimeParam = new RequestParam();
        $runtimeParam->name = "bub";
        $runtimeParam->requirements = '\d+';
        $runtimeParam->description = 'The bub';
        $queryFetcher->addParam($runtimeParam);

        $this->assertEquals(10, $queryFetcher->get('bub'));
        $this->assertEquals(array('foo' => '1', 'bar' => '2', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null, 'bub' => 10, 'arr' => array(), 'arr_null_strict' => array(), 'moo' => ''), $queryFetcher->all());
    }

    public function testValidatesConfiguredParamStrictly()
    {
        $constraint = new Regex(array(
            'pattern' => '#^\d+$#xsu',
            'message' => "Query parameter value '354', does not match requirements '\\d+'",
        ));

        $this->validator->expects($this->once())
            ->method('validateValue')
            ->with('354', $constraint)
        ;

        $queryFetcher = $this->getParamFetcher(array('boozz' => 354), array());
        $queryFetcher->setController($this->controller);
        $this->assertEquals(354, $queryFetcher->get('boozz', true));

        $queryFetcher = $this->getParamFetcher(array(), array());
        $queryFetcher->setController($this->controller);
        try {
            $queryFetcher->get('boozz', true);
            $this->fail('Fetching get() in strict mode with no default value did not throw an exception');
        } catch (HttpException $e) {}

        $queryFetcher = $this->getParamFetcher(array(), array());
        $queryFetcher->setController($this->controller);
        $this->assertNull($queryFetcher->get('biz', true));
    }

    /**
     * Throw exception on invalid parameters.
     * @dataProvider exceptionOnValidatesFailureDataProvider
     */
    public function testExceptionOnValidatesFailure($query, $request, $param, \Closure $callback = null)
    {
        if (null !== $callback) {
            $self = $this;
            $validator = $this->validator;
            $callback($validator, $self);
        }

        $queryFetcher = $this->getParamFetcher($query, $request);
        $queryFetcher->setController($this->controller);

        try {
            try {
                $queryFetcher->get($param, true);
                $this->fail('Fetching get() in strict mode did not throw an exception');
            } catch (HttpException $e) {
                try {
                    $queryFetcher->all(true);
                    $this->fail('Fetching all() in strict mode did not throw an exception');
                } catch (HttpException $e) {
                    return;
                }
            }
        } catch (\Exception $e) {
            $this->fail('Fetching in strict mode did not throw an Symfony\Component\HttpKernel\Exception\HttpException');
        }
    }

    /**
     * @return array Data
     */
    public static function exceptionOnValidatesFailureDataProvider()
    {
        return array(
            array( // test missing 'arr' request param of array type
                array('boozz' => 'foo'),
                array('bar' => 'foo', 'baz' => 'foo'),
                'arr',
            ),
            array( // test missing strict param
                array(),
                array(),
                'bar',
            ),
            array( // test invalid strict param
                array(),
                array('bar' => 'foo'),
                'bar',
                function (\PHPUnit_Framework_MockObject_MockObject $validator, \PHPUnit_Framework_TestCase $self) {
                    $errors = new ConstraintViolationList(array(
                        new ConstraintViolation("expected error", null, array(), null, null, null),
                    ));

                    $validator->expects($self->at(0))
                        ->method('validateValue')
                        ->with('foo', new Regex(array('pattern' => '#^\\d+$#xsu', 'message' => "Request parameter value 'foo', does not match requirements '\\d+'")), null)
                        ->will($self->returnValue($errors));

                    $validator->expects($self->at(1))
                        ->method('validateValue')
                        ->with('foo', new Regex(array('pattern' => '#^\\d+$#xsu', 'message' => "Request parameter value 'foo', does not match requirements '\\d+'")), null)
                        ->will($self->returnValue($errors));
                },
            ),
            array( // test missing strict param with lax requirement
                array(),
                array('baz' => 'foo'),
                'baz',
                function (\PHPUnit_Framework_MockObject_MockObject $validator, \PHPUnit_Framework_TestCase $self) {
                    $errors = new ConstraintViolationList(array(
                        new ConstraintViolation("expected error", null, array(), null, null, null),
                    ));

                    $validator->expects($self->at(0))
                        ->method('validateValue')
                        ->with('foo', new Regex(array('pattern' => '#^\\d?$#xsu', 'message' => "Request parameter value 'foo', does not match requirements '\\d?'")), null)
                        ->will($self->returnValue($errors));
                },
            ),
        );
    }

    /**
     * @expectedException        LogicException
     * @expectedExceptionMessage Controller and method needs to be set via setController
     */
    public function testExceptionOnRequestWithoutController()
    {
        $queryFetcher = new ParamFetcher($this->paramReader, new Request(), $this->violationFormatter, $this->validator);
        $queryFetcher->get('none', '42');
    }

    /**
     * @expectedException        LogicException
     * @expectedExceptionMessage Controller and method needs to be set via setController
     */
    public function testExceptionOnNoController()
    {
        $queryFetcher = $this->getParamFetcher();
        $queryFetcher->setController(array());
        $queryFetcher->get('none', '42');
    }

    /**
     * @expectedException        LogicException
     * @expectedExceptionMessage Controller needs to be set as a class instance (closures/functions are not supported)
     */
    public function testExceptionOnNonController()
    {
        $queryFetcher = $this->getParamFetcher();
        $queryFetcher->setController(array('foo', 'bar'));
        $queryFetcher->get('none', '42');
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage No @QueryParam/@RequestParam configuration for parameter 'none'.
     */
    public function testExceptionOnNonConfiguredParameter()
    {
        $queryFetcher = $this->getParamFetcher();
        $queryFetcher->setController($this->controller);
        $queryFetcher->get('none', '42');
    }

    public function testKeyPrecedenceOverName()
    {
        $queryFetcher = $this->getParamFetcher(array('business' => 5));
        $queryFetcher->setController($this->controller);
        $this->assertEquals(5, $queryFetcher->get('biz'));
    }

    /**
     * Test an Exception is thrown in strict mode
     */
    public function testConstraintThrowExceptionInStrictMode()
    {
        $errors = new ConstraintViolationList(array(
            new ConstraintViolation("expected message 1", null, array(), null, null, null),
            new ConstraintViolation("expected message 2", null, array(), null, null, null),
        ));

        $this->validator->expects($this->once())
            ->method('validateValue')
            ->with('foobar', $this->constraint)
            ->will($this->returnValue($errors));

        $param = new QueryParam();
        $param->name = 'bizoo';
        $param->strict = true;
        $param->requirements = $this->constraint;
        $param->description = 'A requirements param';

        $request = new Request(array('bizoo' => 'foobar'), array(), array('_controller' => __CLASS__.'::stubAction'));
        $reader  = $this->getMockBuilder('FOS\RestBundle\Request\ParamReader')
            ->disableOriginalConstructor()
            ->getMock();

        $reader->expects($this->any())
            ->method('read')
            ->will($this->returnValue(array('bizoo' => $param)));

        $this->violationFormatter->expects($this->once())
            ->method('formatList')
            ->will($this->returnValue('foobar'));

        $this->setExpectedException(
            "\\Symfony\\Component\\HttpKernel\\Exception\\BadRequestHttpException",
            "foobar"
        );

        $queryFetcher =  new ParamFetcher($reader, $request, $this->violationFormatter, $this->validator);
        $queryFetcher->setController($this->controller);
        $queryFetcher->get('bizoo');
    }

    /**
     * Test that the default value is returned in safe mode
     */
    public function testConstraintReturnDefaultInSafeMode()
    {
        $violation1 = $this->getMockBuilder('Symfony\Component\Validator\ConstraintViolation')
            ->disableOriginalConstructor()
            ->getMock();

        $violation1->expects($this->never())->method('getMessage');

        $this->validator->expects($this->once())
            ->method('validateValue')
            ->with('foobar', $this->constraint)
            ->will($this->returnValue(array($violation1)));

        $param = new QueryParam();
        $param->name = 'bizoo';
        $param->requirements = $this->constraint;
        $param->default = 'expected';
        $param->description = 'A requirements param';

        $request = new Request(array('bizoo' => 'foobar'), array(), array('_controller' => __CLASS__.'::stubAction'));
        $reader  = $this->getMockBuilder('FOS\RestBundle\Request\ParamReader')
            ->disableOriginalConstructor()
            ->getMock();

        $reader->expects($this->any())
            ->method('read')
            ->will($this->returnValue(array('bizoo' => $param)));

        $queryFetcher =  new ParamFetcher($reader, $request, $this->violationFormatter, $this->validator);
        $queryFetcher->setController($this->controller);
        $this->assertEquals('expected', $queryFetcher->get('bizoo'));
    }

    /**
     * Test a succesful return with a requirements
     */
    public function testConstraintOk()
    {
        $this->validator->expects($this->once())
            ->method('validateValue')
            ->with('foobar', $this->constraint)
            ->will($this->returnValue(array()));

        $param = new QueryParam();
        $param->name = 'bizoo';
        $param->requirements = $this->constraint;
        $param->default = 'not expected';
        $param->description = 'A requirements param';

        $request = new Request(array('bizoo' => 'foobar'), array(), array('_controller' => __CLASS__.'::stubAction'));
        $reader  = $this->getMockBuilder('FOS\RestBundle\Request\ParamReader')
            ->disableOriginalConstructor()
            ->getMock();

        $reader->expects($this->any())
            ->method('read')
            ->will($this->returnValue(array('bizoo' => $param)));

        $queryFetcher =  new ParamFetcher($reader, $request, $this->violationFormatter, $this->validator);
        $queryFetcher->setController($this->controller);
        $this->assertEquals('foobar', $queryFetcher->get('bizoo'));
    }

    /**
     * Test that we can use deep array structure with a requirements
     */
    public function testDeepArrayAllowedWithConstraint()
    {
        $this->validator->expects($this->once())
            ->method('validateValue')
            ->with(array('foo' => array('b', 'a', 'r')), $this->constraint)
            ->will($this->returnValue(array()));

        $param = new QueryParam();
        $param->name = 'bizoo';
        $param->requirements = $this->constraint;
        $param->default = 'not expected';
        $param->description = 'A requirements param';

        $request = new Request(array('bizoo' => array('foo' => array('b', 'a', 'r'))), array(), array('_controller' => __CLASS__.'::stubAction'));
        $reader  = $this->getMockBuilder('FOS\RestBundle\Request\ParamReader')
            ->disableOriginalConstructor()
            ->getMock();

        $reader->expects($this->any())
            ->method('read')
            ->will($this->returnValue(array('bizoo' => $param)));

        $queryFetcher =  new ParamFetcher($reader, $request, $this->violationFormatter, $this->validator);
        $queryFetcher->setController($this->controller);
        $this->assertSame(array('foo' => array('b', 'a', 'r')), $queryFetcher->get('bizoo'));
    }

    /**
     * @expectedException        \RuntimeException
     * @expectedExceptionMessage The ParamFetcher requirements feature requires the symfony/validator component.
     */
    public function testNullValidatorWithRequirements()
    {
        $param = new QueryParam();
        $param->name = 'bizoo';
        $param->requirements = '\d+';
        $param->default = 'not expected';
        $param->description = 'A requirements param';

        $request = new Request(array('bizoo' => 'foobar'), array(), array('_controller' => __CLASS__.'::stubAction'));
        $reader  = $this->getMockBuilder('FOS\RestBundle\Request\ParamReader')
            ->disableOriginalConstructor()
            ->getMock();

        $reader->expects($this->any())
            ->method('read')
            ->will($this->returnValue(array('bizoo' => $param)));

        $queryFetcher =  new ParamFetcher($reader, $request, $this->violationFormatter);
        $queryFetcher->setController($this->controller);
        $queryFetcher->get('bizoo');
    }

    public function testNullValidatorWithoutRequirements()
    {
        $param = new QueryParam();
        $param->name = 'bizoo';
        $param->default = 'not expected';
        $param->description = 'A param without requirement nor validator';

        $request = new Request(array('bizoo' => 'foobar'), array(), array('_controller' => __CLASS__.'::stubAction'));
        $reader  = $this->getMockBuilder('FOS\RestBundle\Request\ParamReader')
            ->disableOriginalConstructor()
            ->getMock();

        $reader->expects($this->any())
            ->method('read')
            ->will($this->returnValue(array('bizoo' => $param)));

        $queryFetcher =  new ParamFetcher($reader, $request, $this->violationFormatter);
        $queryFetcher->setController($this->controller);
        $this->assertEquals('foobar', $queryFetcher->get('bizoo'));
    }
}
