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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

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

    private function createUploadedFile($path, $originalName, $mimeType = null, $error = null, $test = false)
    {
        $ref = new \ReflectionClass(UploadedFile::class);
        $params = $ref->getConstructor()->getParameters();

        if ('error' === $params[3]->getName()) {
            // symfony 4 has removed the $size param
            return new UploadedFile(
                $path,
                $originalName,
                $mimeType,
                $error,
                $test
            );
        } else {
            return new UploadedFile(
                $path,
                $originalName,
                $mimeType,
                filesize($path),
                $error,
                $test
            );
        }
    }

    protected function setUp(): void
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
        $this->client->request('POST', '/params/test?foo=quz', ['raw' => $this->validRaw]);
        $this->assertArraySubset([
            'before' => ['foo' => 'quz', 'bar' => 'foo'],
            'during' => ['raw' => $this->validRaw, 'map' => 'invalid2 %', 'bar' => null],
            'after' => ['foo' => 'quz', 'bar' => 'foo'],
        ], $this->getData());
    }

    public function testFileParamWithErrors()
    {
        $image = $this->createUploadedFile(
            'Tests/Fixtures/Asset/cat.jpeg',
            $singleFileName = 'cat.jpeg',
            'image/jpeg',
            7
        );

        $this->client->request('POST', '/file/test', [], ['single_file' => $image]);

        $this->assertEquals([
            'single_file' => 'noFile',
        ], $this->getData());
    }

    public function testFileParam()
    {
        $image = $this->createUploadedFile(
            'Tests/Fixtures/Asset/cat.jpeg',
            $singleFileName = 'cat.jpeg',
            'image/jpeg'
        );

        $this->client->request('POST', '/file/test', [], ['single_file' => $image]);

        $this->assertEquals([
            'single_file' => $singleFileName,
        ], $this->getData());
    }

    public function testFileParamNull()
    {
        $this->client->request('POST', '/file/test', [], []);

        $this->assertEquals([
            'single_file' => 'noFile',
        ], $this->getData());
    }

    public function testFileParamArrayNullItem()
    {
        $images = [
            $this->createUploadedFile(
                'Tests/Fixtures/Asset/cat.jpeg',
                $imageName = 'cat.jpeg',
                'image/jpeg'
            ),
            $this->createUploadedFile(
                'Tests/Fixtures/Asset/bar.txt',
                $txtName = 'bar.txt',
                'text/plain'
            ),
        ];

        $this->client->request('POST', '/file/collection/test', [], ['array_files' => $images]);

        $this->assertEquals([
            'array_files' => [$imageName, $txtName],
        ], $this->getData());
    }

    public function testFileParamImageConstraintArray()
    {
        $images = [
            $this->createUploadedFile(
                'Tests/Fixtures/Asset/cat.jpeg',
                $imageName = 'cat.jpeg',
                'image/jpeg'
            ),
            $this->createUploadedFile(
                'Tests/Fixtures/Asset/cat.jpeg',
                $imageName2 = 'cat.jpeg',
                'image/jpeg'
            ),
        ];

        $this->client->request('POST', '/image/collection/test', [], ['array_images' => $images]);

        $this->assertEquals([
            'array_images' => [$imageName, $imageName2],
        ], $this->getData());
    }

    public function testFileParamImageConstraintArrayException()
    {
        $images = [
            $this->createUploadedFile(
                'Tests/Fixtures/Asset/cat.jpeg',
                $imageName = 'cat.jpeg',
                'image/jpeg'
            ),
            $this->createUploadedFile(
                'Tests/Fixtures/Asset/bar.txt',
                $file = 'bar.txt',
                'plain/text'
            ),
        ];

        $this->client->request('POST', '/image/collection/test', [], ['array_images' => $images]);

        $this->assertEquals([
            'array_images' => 'NotAnImage',
        ], $this->getData());
    }

    public function testValidQueryParameter()
    {
        $this->client->request('POST', '/params?foz=val1');

        $this->assertArraySubset(['foz' => ''], $this->getData());
    }

    public function testIncompatibleQueryParameter()
    {
        try {
            $this->client->request('POST', '/params?foz=val1&baz=val2');

            // SF >= 4.4
            $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
            $this->assertContains('\\"baz\\" param is incompatible with foz param.', $this->client->getResponse()->getContent());
        } catch (BadRequestHttpException $e) {
            // SF < 4.4
            $this->assertEquals('"baz" param is incompatible with foz param.', $e->getMessage());
        }
    }

    protected function getData()
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
