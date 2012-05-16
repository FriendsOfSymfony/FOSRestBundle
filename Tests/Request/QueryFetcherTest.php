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
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Request\QueryParamReader;
use FOS\RestBundle\Request\QueryFetcher;
use Symfony\Component\HttpFoundation\Request;

/**
 * QueryParamReader test.
 *
 * @author Alexander <iam.asm89@gmail.com>
 */
class QueryFetcherTest extends \PHPUnit_Framework_TestCase
{
    private $controller;
    private $queryParamReader;

    /**
     * Test setup.
     */
    public function setup()
    {
        $this->controller = array(new \stdClass(), 'indexAction');

        $this->queryParamReader = $this->getMockBuilder('\FOS\RestBundle\Request\QueryParamReader')
            ->disableOriginalConstructor()
            ->getMock();

        $annotations = array();
        $annotations['foo'] = new QueryParam;
        $annotations['foo']->name = 'foo';
        $annotations['foo']->requirements = '\d+';
        $annotations['foo']->default = '1';
        $annotations['foo']->description = 'The foo';

        $annotations['bar'] = new QueryParam;
        $annotations['bar']->name = 'bar';
        $annotations['bar']->requirements = '\d+';
        $annotations['bar']->default = '1';
        $annotations['bar']->description = 'The bar';

        $this->queryParamReader
            ->expects($this->any())
            ->method('read')
            ->will($this->returnValue($annotations));
    }

    /**
     * Get a query fetcher.
     *
     * @param array $query      Query parameters for the request.
     * @param array $attributes Attributes for the request.
     *
     * @return QueryFetcher
     */
    public function getQueryFetcher($query = array(), $attributes = null)
    {
        $attributes = $attributes ?: array('_controller' => __CLASS__.'::stubAction');

        $request = new Request($query, array(), $attributes);

        return new QueryFetcher($this->queryParamReader, $request);
    }

    /**
     * Test valid parameters.
     *
     * @param string $expected Expected query parameter value.
     * @param string $expectedAll Expected query parameter values.
     * @param array  $query    Query parameters for the request.
     *
     * @dataProvider validatesConfiguredQueryParamDataProvider
     */
    public function testValidatesConfiguredQueryParam($expected, $expectedAll, $query)
    {
        $queryFetcher = $this->getQueryFetcher($query);
        $queryFetcher->setController($this->controller);
        $this->assertEquals($expected, $queryFetcher->get('foo'));
        $this->assertEquals($expectedAll, $queryFetcher->all());
    }

    /**
     * Data provider for the valid parameters test.
     *
     * @return array Data
     */
    public static function validatesConfiguredQueryParamDataProvider()
    {
        return array(
            array('1', array('foo' => '1', 'bar' => '1'), array('foo' => '1')),
            array('42', array('foo' => '42', 'bar' => '1'), array('foo' => '42')),
            array('1', array('foo' => '1', 'bar' => '1'), array('foo' => 'bar')),
        );
    }

    /**
     * Throw exception on invalid parameters.
     */
    public function testExceotionOnValidatesFailure()
    {
        $queryFetcher = $this->getQueryFetcher(array('foo' => 'bar'));
        $queryFetcher->setController($this->controller);

        try {
            try {
                $queryFetcher->get('foo', true);
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
     * @expectedException        LogicException
     * @expectedExceptionMessage Controller and method needs to be set via setController
     */
    public function testExceptionOnRequestWithoutController()
    {
        $queryFetcher = new QueryFetcher($this->queryParamReader, new Request());
        $queryFetcher->get('qux', '42');
    }

    /**
     * @expectedException        LogicException
     * @expectedExceptionMessage Controller and method needs to be set via setController
     */
    public function testExceptionOnNoController()
    {
        $queryFetcher = $this->getQueryFetcher();
        $queryFetcher->setController(array());
        $queryFetcher->get('qux', '42');
    }

    /**
     * @expectedException        LogicException
     * @expectedExceptionMessage Controller needs to be set as a class instance (closures/functions are not supported)
     */
    public function testExceptionOnNonController()
    {
        $queryFetcher = $this->getQueryFetcher();
        $queryFetcher->setController(array('foo', 'bar'));
        $queryFetcher->get('qux', '42');
    }

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage No @QueryParam configuration for parameter 'qux'.
     */
    public function testExceptionOnNonConfiguredQueryParameter()
    {
        $queryFetcher = $this->getQueryFetcher();
        $queryFetcher->setController($this->controller);
        $queryFetcher->get('qux', '42');
    }
}
