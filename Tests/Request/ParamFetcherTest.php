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

/**
 * ParamFetcher test.
 *
 * @author Alexander <iam.asm89@gmail.com>
 * @author Boris Guéry <guery.b@gmail.com>
 */
class ParamFetcherTest extends \PHPUnit_Framework_TestCase
{
    private $controller;
    private $paramReader;

    /**
     * Test setup.
     */
    public function setup()
    {
        $this->controller = array(new \stdClass(), 'indexAction');

        $this->paramReader = $this->getMockBuilder('\FOS\RestBundle\Request\ParamReader')
            ->disableOriginalConstructor()
            ->getMock();

        $annotations = array();
        $annotations['foo'] = new QueryParam;
        $annotations['foo']->name = 'foo';
        $annotations['foo']->requirements = '\d+';
        $annotations['foo']->default = '1';
        $annotations['foo']->description = 'The foo';
        $annotations['foo']->nullable = false;

        $annotations['bar'] = new RequestParam;
        $annotations['bar']->name = 'bar';
        $annotations['bar']->requirements = '\d+';
        $annotations['bar']->description = 'The bar';

        $annotations['baz'] = new RequestParam;
        $annotations['baz']->name = 'baz';
        $annotations['baz']->requirements = '\d?';

        $annotations['buzz'] = new QueryParam;
        $annotations['buzz']->array = true;
        $annotations['buzz']->name = 'buzz';
        $annotations['buzz']->requirements = '\d+';
        $annotations['buzz']->default = '1';
        $annotations['buzz']->nullable = false;
        $annotations['buzz']->description = 'An array';

        $annotations['boo'] = new QueryParam;
        $annotations['boo']->array = true;
        $annotations['boo']->name = 'boo';
        $annotations['boo']->description = 'An array with no default value';

        $annotations['boozz'] = new QueryParam;
        $annotations['boozz']->name = 'boozz';
        $annotations['boozz']->requirements = '\d+';
        $annotations['boozz']->description = 'A scalar param with no default value (an optional limit param for example)';

        $annotations['biz'] = new QueryParam;
        $annotations['biz']->name = 'biz';
        $annotations['biz']->requirements = '\d+';
        $annotations['biz']->default = null;
        $annotations['biz']->nullable = true;
        $annotations['biz']->description = 'A scalar param with an explicitly defined null default';

        $this->paramReader
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValue($annotations));
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

        return new ParamFetcher($this->paramReader, $req);
    }

    /**
     * Test valid parameters.
     *
     * @param string $param       which param to test
     * @param string $expected    Expected query parameter value.
     * @param string $expectedAll Expected query parameter values.
     * @param array  $query       Query parameters for the request.
     * @param array  $request     Request parameters for the request.
     *
     * @dataProvider validatesConfiguredParamDataProvider
     */
    public function testValidatesConfiguredParam($param, $expected, $expectedAll, $query, $request)
    {
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
                array('foo' => '1', 'bar' => '2', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null),
                array(),
                array('bar' => '2', 'baz' => '4'),
            ),
            array( // pass Param in GET
                'foo',
                '42',
                array('foo' => '42', 'bar' => '2', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null),
                array('foo' => '42'),
                array('bar' => '2', 'baz' => '4'),
            ),
            array( // check that invalid non-strict params take default value
                'foo',
                '1',
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null),
                array('foo' => 'bar'),
                array('bar' => '1', 'baz' => '4'),
            ),
            array( // invalid array
                'buzz',
                array(1),
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null),
                array('buzz' => 'invaliddata'),
                array('bar' => '1', 'baz' => '4'),
            ),
            array( // invalid array (multiple depth)
                'buzz',
                array(1),
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'baz' => '4', 'buzz' => array(1), 'boo' => array(), 'boozz' => null, 'biz' => null),
                array('buzz' => array(array(1))),
                array('bar' => '1', 'baz' => '4'),
            ),

            array( // multiple array
                'buzz',
                array(2, 3, 4),
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array(), 'boozz' => null, 'biz' => null),
                array('buzz' => array(2, 3, 4)),
                array('bar' => '1', 'baz' => '4'),
            ),
            array( // multiple array with one invalid value
                'buzz',
                array(2, 1, 4),
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'baz' => '4', 'buzz' => array(2, 1, 4), 'boo' => array(), 'boozz' => null, 'biz' => null),
                array('buzz' => array(2, 'invaliddata', 4)),
                array('bar' => '1', 'baz' => '4'),
            ),
            array(  // Array not provided in GET query
                'boo',
                array(),
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array(), 'boozz' => null, 'biz' => null),
                array('buzz' => array(2, 3, 4)),
                array('bar' => '1', 'baz' => '4'),
            ),
            array(  // QueryParam provided in GET query but as a scalar
                'boo',
                array(),
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array(), 'boozz' => null, 'biz' => null),
                array('buzz' => array(2, 3, 4), 'boo' => 'scalar'),
                array('bar' => '1', 'baz' => '4'),
            ),
            array(  // QueryParam provided in GET query with valid values
                'boo',
                array('1', 'foo', 5),
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5), 'boozz' => null, 'biz' => null),
                array('buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5)),
                array('bar' => '1', 'baz' => '4'),
            ),
            array(  // QueryParam provided in GET query with valid values
                'boozz',
                null,
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5), 'boozz' => null, 'biz' => null),
                array('buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5)),
                array('bar' => '1', 'baz' => '4'),
            ),
            array(  // QueryParam provided in GET query with valid values
                'boozz',
                5,
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'baz' => '4', 'buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5), 'boozz' => 5, 'biz' => null),
                array('buzz' => array(2, 3, 4), 'boo' => array('1', 'foo', 5), 'boozz' => 5),
                array('bar' => '1', 'baz' => '4', 'boozz' => 5),
            )
        );
    }

    public function testValidatesConfiguredParamStrictly()
    {
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
    public function testExceptionOnValidatesFailure($query, $request, $param)
    {
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
            array( // test missing strict param
                array(),
                array(),
                'bar'
            ),
            array( // test invalid strict param
                array(),
                array('bar' => 'foo'),
                'bar'
            ),
            array( // test missing strict param with lax requirement
                array(),
                array('baz' => 'foo'),
                'baz'
            ),
        );
    }

    /**
     * @expectedException        LogicException
     * @expectedExceptionMessage Controller and method needs to be set via setController
     */
    public function testExceptionOnRequestWithoutController()
    {
        $queryFetcher = new ParamFetcher($this->paramReader, new Request());
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
}
