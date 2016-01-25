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

/**
 * @author Ener-Getick <egetick@gmail.com>
 */
class ParamFetcherTest extends WebTestCase
{
    private $validRaw = [
        'foo' => 'raw',
        'bar' => 'foo',
    ];
    private $validMap = [
        'foo' => 'map',
        'foobar' => 'foo',
    ];

    public function setUp()
    {
        $this->client = $this->createClient(['test_case' => 'ParamFetcher']);
    }

    public function testDefaultParameters()
    {
        $this->client->request('POST', '/params');

        $this->assertEquals(['raw' => 'invalid', 'map' => 'invalid2 %', 'bar' => null], $this->getData());
    }

    public function testValidRawParameter()
    {
        $this->client->request('POST', '/params', ['raw' => $this->validRaw, 'map' => $this->validMap]);

        $this->assertEquals(['raw' => $this->validRaw, 'map' => 'invalid2 %', 'bar' => null], $this->getData());
    }

    public function testValidMapParameter()
    {
        $map = [
            'foo' => $this->validMap,
            'bar' => $this->validMap,
        ];
        $this->client->request('POST', '/params', ['raw' => 'bar', 'map' => $map]);

        $this->assertEquals(['raw' => 'invalid', 'map' => $map, 'bar' => null], $this->getData());
    }

    public function testFooParameter()
    {
        $value = ['bar foo', 'bar foo'];
        $this->client->request('POST', '/params', ['foo' => $value]);

        $map = array(
            'foo' => $this->validMap,
            'bar' => $this->validMap,
        );
        $this->client->request('POST', '/params', array('raw' => 'bar', 'map' => $map));

        $this->assertEquals(array('raw' => 'invalid', 'map' => $map, 'bar' => null), $this->getData());
    }

    public function testWithSubRequests()
    {
        $this->client->request('POST', '/params/test?foo=quz', array('raw' => $this->validRaw));
        $this->assertEquals(array(
            'before' => array('foo' => 'quz', 'bar' => 'foo'),
            'during' => array('raw' => $this->validRaw, 'map' => 'invalid2 %', 'bar' => null),
            'after' => array('foo' => 'quz', 'bar' => 'foo'),
        ), $this->getData());
    }

    protected function getData()
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
