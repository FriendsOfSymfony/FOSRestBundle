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

        $this->assertArraySubset(['raw' => 'invalid', 'map' => 'invalid2 %', 'bar' => null], $this->getData());
    }

    public function testValidRawParameter()
    {
        $this->client->request('POST', '/params', ['raw' => $this->validRaw, 'map' => $this->validMap]);

        $this->assertArraySubset(['raw' => $this->validRaw, 'map' => 'invalid2 %', 'bar' => null], $this->getData());
    }

    public function testValidMapParameter()
    {
        $map = [
            'foo' => $this->validMap,
            'bar' => $this->validMap,
        ];
        $this->client->request('POST', '/params', ['raw' => 'bar', 'map' => $map, 'bar' => 'bar foo']);

        $this->assertArraySubset(['raw' => 'invalid', 'map' => $map, 'bar' => 'bar foo'], $this->getData());
    }

    public function testWithSubRequests()
    {
        $this->client->request('POST', '/params/test?foo=quz', array('raw' => $this->validRaw));
        $this->assertArraySubset(array(
            'before' => array('foo' => 'quz', 'bar' => 'foo'),
            'during' => array('raw' => $this->validRaw, 'map' => 'invalid2 %', 'bar' => null),
            'after' => array('foo' => 'quz', 'bar' => 'foo'),
        ), $this->getData());
    }

    public function testValidQueryParameter()
    {
        $this->client->request('POST', '/params?foz=val1');
    }

    /**
     * @expectedException Symfony\Component\HttpKernel\Exception\BadRequestHttpException
     * @expectedExceptionMessage 'baz' param is incompatible with foz param.
     */
    public function testIncompatibleQueryParameter()
    {
        $this->client->request('POST', '/params?foz=val1&baz=val2');
    }

    protected function getData()
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
