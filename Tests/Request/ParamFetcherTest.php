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

use FOS\RestBundle\Controller\Annotations\NamePrefix;
use FOS\RestBundle\Controller\Annotations\Param;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\QueryParamReader;
use FOS\RestBundle\Request\ParamFetcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * QueryParamReader test.
 *
 * @author Alexander <iam.asm89@gmail.com>
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

        $annotations['bar'] = new RequestParam;
        $annotations['bar']->name = 'bar';
        $annotations['bar']->requirements = '\d+';
        $annotations['bar']->description = 'The bar';

        $annotations['baz'] = new Param;
        $annotations['baz']->name = 'baz';
        $annotations['baz']->requirements = '\d+';
        $annotations['baz']->description = 'The baz';

        $annotations['qux'] = new RequestParam;
        $annotations['qux']->name = 'qux';
        $annotations['qux']->requirements = '\d?';

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
     * @param string $expected Expected query parameter value.
     * @param string $expectedAll Expected query parameter values.
     * @param array  $query    Query parameters for the request.
     * @param array  $request  Request parameters for the request.
     *
     * @dataProvider validatesConfiguredParamDataProvider
     */
    public function testValidatesConfiguredParam($expected, $expectedAll, $query, $request)
    {
        $queryFetcher = $this->getParamFetcher($query, $request);
        $queryFetcher->setController($this->controller);
        $this->assertEquals($expected, $queryFetcher->get('foo'));
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
            array( // pass Param in POST, check that non-strict missing params take default value
                '1',
                array('foo' => '1', 'bar' => '2', 'baz' => '3', 'qux' => '4'),
                array(),
                array('bar' => '2', 'baz' => '3', 'qux' => '4'),
            ),
            array( // pass Param in GET
                '42',
                array('foo' => '42', 'bar' => '2', 'baz' => '3', 'qux' => '4'),
                array('foo' => '42', 'baz' => '3'),
                array('bar' => '2', 'qux' => '4'),
            ),
            array( // check that invalid non-strict params take default value
                '1',
                array('foo' => '1', 'bar' => '1', 'baz' => '1', 'qux' => '4'),
                array('foo' => 'bar'),
                array('bar' => '1', 'baz' => '1', 'qux' => '4'),
            ),
        );
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
            } catch (\RuntimeException $e) {
                try {
                    $queryFetcher->all(true);
                    $this->fail('Fetching all() in strict mode did not throw an exception');
                } catch (\RuntimeException $e) {
                    return;
                }
            }
        } catch (\Exception $e) {
            $this->fail('Fetching in strict mode did not throw an \RuntimeException');
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
                array('qux' => 'foo'),
                'qux'
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
     * @expectedExceptionMessage No @*Param configuration for parameter 'none'.
     */
    public function testExceptionOnNonConfiguredParameter()
    {
        $queryFetcher = $this->getParamFetcher();
        $queryFetcher->setController($this->controller);
        $queryFetcher->get('none', '42');
    }
}
