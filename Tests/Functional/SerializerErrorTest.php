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

use Symfony\Component\ErrorHandler\ErrorRenderer\SerializerErrorRenderer;

/**
 * Test class for serialization errors and exceptions.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class SerializerErrorTest extends WebTestCase
{
    public static function tearDownAfterClass(): void
    {
        self::deleteTmpDir('FlattenExceptionHandlerLegacyFormat');
        self::deleteTmpDir('FlattenExceptionHandlerRfc7807Format');
        self::deleteTmpDir('FlattenExceptionNormalizerLegacyFormat');
        self::deleteTmpDir('FlattenExceptionNormalizerLegacyFormatDebug');
        self::deleteTmpDir('FlattenExceptionNormalizerRfc7807Format');
        self::deleteTmpDir('FormErrorHandler');
        self::deleteTmpDir('FormErrorNormalizer');
        self::deleteTmpDir('Serializer');
        self::deleteTmpDir('JMSSerializer');
        parent::tearDownAfterClass();
    }

    /**
     * @group legacy
     *
     * @dataProvider serializeExceptionJsonProvider
     */
    public function testSerializeExceptionJson($testCase)
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => $testCase, 'debug' => false]);
        $client->request('GET', '/serializer-error/exception.json');

        $this->assertEquals('{"code":500,"message":"Something bad happened."}', $client->getResponse()->getContent());
    }

    public function serializeExceptionJsonProvider()
    {
        return [
            ['Serializer'],
            ['JMSSerializer'],
        ];
    }

    /**
     * @dataProvider serializeExceptionJsonUsingErrorRendererProvider
     */
    public function testSerializeExceptionJsonUsingErrorRenderer(string $testCase, array $expectedJson, string $expectedContentType)
    {
        if (!class_exists(SerializerErrorRenderer::class)) {
            $this->markTestSkipped();
        }

        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => $testCase, 'debug' => false]);
        $client->request('GET', '/serializer-error/exception.json');

        $this->assertStringStartsWith($expectedContentType, $client->getResponse()->headers->get('Content-Type'));
        $this->assertEquals(json_encode($expectedJson), $client->getResponse()->getContent());
    }

    public function serializeExceptionJsonUsingErrorRendererProvider(): array
    {
        return [
            ['FlattenExceptionNormalizerLegacyFormat', [
                'code' => 500,
                'message' => 'Something bad happened.',
            ], 'application/json'],
            ['FlattenExceptionNormalizerRfc7807Format', [
                'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
                'title' => 'An error occurred',
                'status' => 500,
                'detail' => 'Something bad happened.',
            ], 'application/problem+json'],
            ['FlattenExceptionHandlerLegacyFormat', [
                'code' => 500,
                'message' => 'Something bad happened.',
            ], 'application/json'],
            ['FlattenExceptionHandlerRfc7807Format', [
                'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
                'title' => 'An error occurred',
                'status' => 500,
                'detail' => 'Something bad happened.',
            ], 'application/problem+json'],
        ];
    }

    /**
     * @group legacy
     */
    public function testSerializeExceptionJsonWithDebug()
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => 'Debug', 'debug' => false]);
        $client->request('GET', '/serializer-error/unknown_exception.json');

        $this->assertEquals('{"code":500,"message":"Unknown exception message."}', $client->getResponse()->getContent());
    }

    public function testSerializeUnknownExceptionJsonWithDebugUsingErrorRenderer()
    {
        if (!class_exists(SerializerErrorRenderer::class)) {
            $this->markTestSkipped();
        }

        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => 'FlattenExceptionNormalizerLegacyFormatDebug', 'debug' => false]);
        $client->request('GET', '/serializer-error/unknown_exception.json');

        $this->assertEquals('{"code":500,"message":"Unknown exception message."}', $client->getResponse()->getContent());
    }

    /**
     * @group legacy
     */
    public function testSerializeExceptionJsonWithoutDebug()
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => 'Serializer', 'debug' => false]);
        $client->request('GET', '/serializer-error/unknown_exception.json');

        $this->assertEquals('{"code":500,"message":"Internal Server Error"}', $client->getResponse()->getContent());
    }

    public function testSerializeUnknownExceptionJsonWithoutDebugUsingErrorRenderer()
    {
        if (!class_exists(SerializerErrorRenderer::class)) {
            $this->markTestSkipped();
        }

        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => 'FlattenExceptionNormalizerLegacyFormat', 'debug' => false]);
        $client->request('GET', '/serializer-error/unknown_exception.json');

        $this->assertEquals('{"code":500,"message":"Internal Server Error"}', $client->getResponse()->getContent());
    }

    /**
     * @dataProvider serializeExceptionCodeMappedToResponseStatusCodeJsonProvider
     */
    public function testSerializeExceptionCodeMappedToResponseStatusCodeJsonUsingErrorRenderer(string $testCase, array $expectedJson)
    {
        if (!class_exists(SerializerErrorRenderer::class)) {
            $this->markTestSkipped();
        }

        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => $testCase, 'debug' => false]);
        $client->request('GET', '/serializer-error/invalid-argument-exception.json');

        $this->assertEquals(json_encode($expectedJson), $client->getResponse()->getContent());
    }

    public function serializeExceptionCodeMappedToResponseStatusCodeJsonProvider(): array
    {
        return [
            [
                'FlattenExceptionHandlerLegacyFormat',
                [
                    'code' => 400,
                    'message' => 'Invalid argument given.',
                ],
            ],
            [
                'FlattenExceptionHandlerRfc7807Format',
                [
                    'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
                    'title' => 'An error occurred',
                    'status' => 400,
                    'detail' => 'Invalid argument given.',
                ],
            ],
            [
                'FlattenExceptionNormalizerLegacyFormat',
                [
                    'code' => 400,
                    'message' => 'Invalid argument given.',
                ],
            ],
            [
                'FlattenExceptionNormalizerRfc7807Format',
                [
                    'type' => 'https://tools.ietf.org/html/rfc2616#section-10',
                    'title' => 'An error occurred',
                    'status' => 400,
                    'detail' => 'Invalid argument given.',
                ],
            ],
        ];
    }

    /**
     * @group legacy
     *
     * @dataProvider serializeExceptionXmlProvider
     */
    public function testSerializeExceptionXml($testCase, $expectedContent)
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => $testCase, 'debug' => false]);
        $client->request('GET', '/serializer-error/exception.xml');

        $this->assertXmlStringEqualsXmlString($expectedContent, $client->getResponse()->getContent());
    }

    public function serializeExceptionXmlProvider()
    {
        $expectedSerializerContent = <<<'XML'
<?xml version="1.0"?>
<response><code>500</code><message>Something bad happened.</message></response>

XML;

        $expectedJMSContent = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<result>
  <code>500</code>
  <message><![CDATA[Something bad happened.]]></message>
</result>

XML;

        return [
            ['Serializer', $expectedSerializerContent],
            ['JMSSerializer', $expectedJMSContent],
        ];
    }

    /**
     * @dataProvider serializeExceptionXmlUsingErrorRendererProvider
     */
    public function testSerializeExceptionXmlUsingErrorRenderer(string $testCase, string $expectedContent, string $expectedContentType)
    {
        if (!class_exists(SerializerErrorRenderer::class)) {
            $this->markTestSkipped();
        }

        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => $testCase, 'debug' => false]);
        $client->request('GET', '/serializer-error/exception.xml');

        $this->assertStringStartsWith($expectedContentType, $client->getResponse()->headers->get('Content-Type'));
        $this->assertXmlStringEqualsXmlString($expectedContent, $client->getResponse()->getContent());
    }

    public function serializeExceptionXmlUsingErrorRendererProvider(): array
    {
        $expectedLegacyContent = <<<'XML'
<?xml version="1.0"?>
<response><code>500</code><message>Something bad happened.</message></response>

XML;
        $expectedLegacyJmsContent = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<result>
  <code>500</code>
  <message><![CDATA[Something bad happened.]]></message>
</result>

XML;
        $expectedRfc7807Content = <<<'XML'
<?xml version="1.0"?>
<response>
  <type>https://tools.ietf.org/html/rfc2616#section-10</type>
  <title>An error occurred</title>
  <status>500</status>
  <detail>Something bad happened.</detail>
</response>

XML;

        return [
            ['FlattenExceptionNormalizerLegacyFormat', $expectedLegacyContent, 'text/xml'],
            ['FlattenExceptionNormalizerRfc7807Format', $expectedRfc7807Content, 'application/problem+xml'],
            ['FlattenExceptionHandlerLegacyFormat', $expectedLegacyJmsContent, 'text/xml'],
            ['FlattenExceptionHandlerRfc7807Format', $expectedRfc7807Content, 'application/problem+xml'],
        ];
    }

    /**
     * @dataProvider invalidFormJsonProvider
     */
    public function testSerializeInvalidFormJson($testCase)
    {
        $client = $this->createClient(['test_case' => $testCase, 'debug' => false]);
        $client->request('GET', '/serializer-error/invalid-form.json');

        $this->assertEquals('{"code":400,"message":"Validation Failed","errors":{"children":{"name":{"errors":["This value should not be blank."]}}}}', $client->getResponse()->getContent());
    }

    public function invalidFormJsonProvider()
    {
        return [
            ['FormErrorHandler'],
            ['FormErrorNormalizer'],
        ];
    }

    /**
     * @dataProvider serializeInvalidFormXmlProvider
     */
    public function testSerializeInvalidFormXml($testCase, $expectedContent)
    {
        $client = $this->createClient(['test_case' => $testCase, 'debug' => false]);
        $client->request('GET', '/serializer-error/invalid-form.xml');

        $this->assertXmlStringEqualsXmlString($expectedContent, $client->getResponse()->getContent());
    }

    public function serializeInvalidFormXmlProvider()
    {
        $expectedSerializerContent = <<<'XML'
<?xml version="1.0"?>
<response><code>400</code><message>Validation Failed</message><errors><children><name><errors>This value should not be blank.</errors></name></children></errors></response>

XML;

        $expectedJMSContent = <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<result>
  <code>400</code>
  <message><![CDATA[Validation Failed]]></message>
  <errors>
    <form name="form">
      <errors/>
      <form name="name">
        <errors>
          <entry><![CDATA[This value should not be blank.]]></entry>
        </errors>
      </form>
    </form>
  </errors>
</result>

XML;

        return [
            ['FormErrorNormalizer', $expectedSerializerContent],
            ['FormErrorHandler', $expectedJMSContent],
        ];
    }
}
