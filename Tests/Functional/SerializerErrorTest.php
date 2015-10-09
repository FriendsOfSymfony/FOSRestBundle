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
     * @dataProvider serializeExceptionJsonProvider
     */
    public function testSerializeExceptionJson($testCase, $expectedContent)
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(array('test_case' => $testCase));
        $client->request('GET', '/serializer-error/exception.json');

        $this->assertEquals($expectedContent, $client->getResponse()->getContent());
    }

    public function serializeExceptionJsonProvider()
    {
        return array(
            array('Serializer', '{"code":500,"message":"Something bad happened.","errors":null}'),
            array('JMSSerializer', '{"code":500,"message":"Something bad happened."}'),
        );
    }

    /**
     * @dataProvider serializeExceptionXmlProvider
     */
    public function testSerializeExceptionXml($testCase, $expectedContent)
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(array('test_case' => $testCase));
        $client->request('GET', '/serializer-error/exception.xml');

        $this->assertEquals($expectedContent, $client->getResponse()->getContent());
    }

    public function serializeExceptionXmlProvider()
    {
        $expectedSerializerContent = <<<XML
<?xml version="1.0"?>
<response><code>500</code><message>Something bad happened.</message><errors/></response>

XML;

        $expectedJMSContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<result xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <code>500</code>
  <message><![CDATA[Something bad happened.]]></message>
  <errors xsi:nil="true"/>
</result>

XML;

        return array(
            array('Serializer', $expectedSerializerContent),
            array('JMSSerializer', $expectedJMSContent),
        );
    }

    /**
     * @dataProvider serializeInvalidFormJsonProvider
     */
    public function testSerializeInvalidFormJson($testCase, $expectedContent)
    {
        $client = $this->createClient(array('test_case' => $testCase));
        $client->request('GET', '/serializer-error/invalid-form.json');

        $this->assertEquals($expectedContent, $client->getResponse()->getContent());
    }

    public function serializeInvalidFormJsonProvider()
    {
        return array(
            array('Serializer', '{"code":400,"message":"Validation Failed","errors":{"children":{"name":{"errors":["This value should not be blank."]}}}}'),
            array('JMSSerializer', '{"code":400,"message":"Validation Failed","errors":{"children":{"name":{"errors":["This value should not be blank."]}}}}'),
        );
    }

    /**
     * @dataProvider serializeInvalidFormXmlProvider
     */
    public function testSerializeInvalidFormXml($testCase, $expectedContent)
    {
        $client = $this->createClient(array('test_case' => $testCase));
        $client->request('GET', '/serializer-error/invalid-form.xml');

        $this->assertEquals($expectedContent, $client->getResponse()->getContent());
    }

    public function serializeInvalidFormXmlProvider()
    {
        $expectedSerializerContent = <<<XML
<?xml version="1.0"?>
<response><code>400</code><message>Validation Failed</message><errors><children><name><errors>This value should not be blank.</errors></name></children></errors></response>

XML;

        $expectedJMSContent = <<<XML
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

        return array(
            array('Serializer', $expectedSerializerContent),
            array('JMSSerializer', $expectedJMSContent),
        );
    }
}
