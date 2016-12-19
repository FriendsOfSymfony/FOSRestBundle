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

use Symfony\Component\HttpFoundation\File\UploadedFile;

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

    public function testFileParam()
    {
        $image = new UploadedFile(
            'Tests/Fixtures/Asset/cat.jpeg',
            $singleFileName = 'cat.jpeg',
            'image/jpeg',
            123
        );

        $this->client->request('POST', '/file/test', array(), array('single_file' => $image));

        $this->assertEquals(array(
            'single_file' => $singleFileName,
        ), $this->getData());
    }

    public function testFileParamNull()
    {
        $this->client->request('POST', '/file/test', array(), array());

        $this->assertEquals(array(
            'single_file' => 'noFile',
        ), $this->getData());
    }

    public function testFileParamArrayNullItem()
    {
        $images = [
            new UploadedFile(
                'Tests/Fixtures/Asset/cat.jpeg',
                $imageName = 'cat.jpeg',
                'image/jpeg',
                1234
            ),
            new UploadedFile(
                'Tests/Fixtures/Asset/bar.txt',
                $txtName = 'bar.txt',
                'text/plain',
                123
            ),
        ];

        $this->client->request('POST', '/file/collection/test', array(), array('array_files' => $images));

        $this->assertEquals(array(
            'array_files' => [$imageName, $txtName],
        ), $this->getData());
    }

    public function testFileParamImageConstraintArray()
    {
        $images = [
            new UploadedFile(
                'Tests/Fixtures/Asset/cat.jpeg',
                $imageName = 'cat.jpeg',
                'image/jpeg',
                12345
            ),
            new UploadedFile(
                'Tests/Fixtures/Asset/cat.jpeg',
                $imageName2 = 'cat.jpeg',
                'image/jpeg',
                1234
            ),
        ];

        $this->client->request('POST', '/image/collection/test', array(), array('array_images' => $images));

        $this->assertEquals(array(
            'array_images' => [$imageName, $imageName2],
        ), $this->getData());
    }

    public function testFileParamImageConstraintArrayException()
    {
        $images = [
            new UploadedFile(
                'Tests/Fixtures/Asset/cat.jpeg',
                $imageName = 'cat.jpeg',
                'image/jpeg',
                12345
            ),
            new UploadedFile(
                'Tests/Fixtures/Asset/bar.txt',
                $file = 'bar.txt',
                'plain/text',
                1234
            ),
        ];

        $this->client->request('POST', '/image/collection/test', array(), array('array_images' => $images));

        $this->assertEquals(array(
            'array_images' => 'NotAnImage',
        ), $this->getData());
    }

    public function testValidQueryParameter()
    {
        $this->client->request('POST', '/params?foz=val1');
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
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
