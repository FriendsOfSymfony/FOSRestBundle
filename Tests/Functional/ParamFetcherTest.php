<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Request;

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class ParamFetcherTest extends WebTestCase
{
    private $validRaw = 'fooraw';
    private $validMap = array(
        'foo' => 'map',
        'foobar' => 'foo',
    );

    public function setUp()
    {
        $this->client = $this->createClient(array('test_case' => 'ParamFetcher'));
    }

    public function testDefaultParameters()
    {
        $this->client->request('POST', '/params');

        $this->assertEquals(array('raw' => 'invalid', 'map' => array('invalid2')), $this->getData());
    }

    public function testValidRawParameter()
    {
        $this->client->request('POST', '/params', array('raw' => $this->validRaw, 'map' => $this->validMap));

        $this->assertEquals(array('raw' => $this->validRaw, 'map' => array('foo' => 'invalid2', 'foobar' => 'invalid2')), $this->getData());
    }

    public function testValidMapParameter()
    {
        $map = array(
            'foo' => $this->validMap,
            'bar' => $this->validMap,
        );
        $this->client->request('POST', '/params', array('raw' => 'bar', 'map' => $map));

        $this->assertEquals(array('raw' => 'invalid', 'map' => $map), $this->getData());
    }

    public function testWithSubRequests()
    {
        $this->client->request('POST', '/params/test?foo=quz', array('raw' => $this->validRaw));
        $this->assertEquals(array(
            'before' => array('foo' => 'quz', 'bar' => 'foo'),
            'during' => array('raw' => $this->validRaw, 'map' => array('invalid2')),
            'after' => array('foo' => 'quz', 'bar' => 'foo'),
        ), $this->getData());
    }

    protected function getData()
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
