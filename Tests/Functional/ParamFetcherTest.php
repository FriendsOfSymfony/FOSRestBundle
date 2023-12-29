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

use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
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

    /**
     * @var KernelBrowser
     */
    private $client;

    private function createUploadedFile($path, $originalName, $mimeType = null, $error = null, $test = false)
    {
        return new UploadedFile(
            $path,
            $originalName,
            $mimeType,
            $error,
            $test
        );
    }

    protected function setUp(): void
    {
        $this->client = $this->createClient(['test_case' => 'ParamFetcher']);
    }

    public function testDefaultParameters()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

        $this->client->request('POST', '/params');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $data = $this->getData();

        foreach (['raw' => 'invalid', 'map' => 'invalid2 %', 'bar' => null] as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertSame($value, $data[$key]);
        }
    }

    public function testValidRawParameter()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

        $this->client->request('POST', '/params', ['raw' => $this->validRaw, 'map' => $this->validMap]);

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $data = $this->getData();
        foreach (['raw' => $this->validRaw, 'map' => 'invalid2 %', 'bar' => null] as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertSame($value, $data[$key]);
        }
    }

    public function testValidMapParameter()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

        $map = [
            'foo' => $this->validMap,
            'bar' => $this->validMap,
        ];

        $this->client->request('POST', '/params', ['raw' => 'bar', 'map' => $map, 'bar' => 'bar foo']);

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $data = $this->getData();
        foreach (['raw' => 'invalid', 'map' => $map, 'bar' => 'bar foo'] as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertSame($value, $data[$key]);
        }
    }

    public function testWithSubRequests()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

        $this->client->request('POST', '/params/test?foo=quz', ['raw' => $this->validRaw]);

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $expected = [
            'before' => ['foo' => 'quz', 'bar' => 'foo'],
            'during' => ['raw' => $this->validRaw, 'map' => 'invalid2 %', 'bar' => null, 'foz' => '', 'baz' => ''],
            'after' => ['foo' => 'quz', 'bar' => 'foo'],
        ];
        $data = $this->getData();
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertSame($value, $data[$key]);
        }
    }

    public function testFileParamWithErrors()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

        $image = $this->createUploadedFile(
            'Tests/Fixtures/Asset/cat.jpeg',
            'cat.jpeg',
            'image/jpeg',
            7
        );

        $this->client->request('POST', '/file/test', [], ['single_file' => $image]);

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $this->assertEquals([
            'single_file' => 'noFile',
        ], $this->getData());
    }

    public function testFileParam()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

        $image = $this->createUploadedFile(
            'Tests/Fixtures/Asset/cat.jpeg',
            $singleFileName = 'cat.jpeg',
            'image/jpeg'
        );

        $this->client->request('POST', '/file/test', [], ['single_file' => $image]);

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $this->assertEquals([
            'single_file' => $singleFileName,
        ], $this->getData());
    }

    public function testFileParamNull()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

        $this->client->request('POST', '/file/test', [], []);

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $this->assertEquals([
            'single_file' => 'noFile',
        ], $this->getData());
    }

    public function testFileParamArrayNullItem()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

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

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $this->assertEquals([
            'array_files' => [$imageName, $txtName],
        ], $this->getData());
    }

    public function testFileParamImageConstraintArray()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

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

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $this->assertEquals([
            'array_images' => [$imageName, $imageName2],
        ], $this->getData());
    }

    public function testFileParamImageConstraintArrayException()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

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

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $this->assertEquals([
            'array_images' => 'NotAnImage',
        ], $this->getData());
    }

    public function testValidQueryParameter()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

        $this->client->request('POST', '/params?foz=val1');

        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'The request resulted in an error.');

        $data = $this->getData();
        foreach (['foz' => ''] as $key => $value) {
            $this->assertArrayHasKey($key, $data);
            $this->assertSame($value, $data[$key]);
        }
    }

    public function testIncompatibleQueryParameter()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

        $this->client->request('POST', '/params?foz=val1&baz=val2');

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $this->assertStringContainsString('\\"baz\\" param is incompatible with foz param.', $this->client->getResponse()->getContent());
    }

    protected function getData()
    {
        return json_decode($this->client->getResponse()->getContent(), true);
    }
}
