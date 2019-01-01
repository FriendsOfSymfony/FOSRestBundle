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
 * Test class for serialization errors and exceptions.
 *
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class SerializerErrorTest extends WebTestCase
{
    /**
     * @dataProvider invalidFormJsonProvider
     */
    public function testSerializeExceptionJson($testCase)
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => $testCase, 'debug' => false]);
        $client->request('GET', '/serializer-error/exception.json');

        $this->assertEquals('{"code":500,"message":"Something bad happened."}', $client->getResponse()->getContent());
    }

    public function testSerializeExceptionJsonWithDebug()
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(array('test_case' => 'Debug', 'debug' => false));
        $client->request('GET', '/serializer-error/unknown_exception.json');

        $this->assertEquals('{"code":500,"message":"Unknown exception message."}', $client->getResponse()->getContent());
    }

    public function testSerializeExceptionJsonWithoutDebug()
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(array('test_case' => 'Serializer', 'debug' => false));
        $client->request('GET', '/serializer-error/unknown_exception.json');

        $this->assertEquals('{"code":500,"message":"Internal Server Error"}', $client->getResponse()->getContent());
    }

    /**
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
            ['Serializer'],
            ['JMSSerializer'],
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
            ['Serializer', $expectedSerializerContent],
            ['JMSSerializer', $expectedJMSContent],
        ];
    }
}
