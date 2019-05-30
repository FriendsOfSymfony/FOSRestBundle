<?php

namespace FOS\RestBundle\Tests\Functional;

class RoutingConditionsTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = $this->createClient(['test_case' => 'RoutingConditions']);
    }

    public function testVersionInPathWithPrefix()
    {
        $this->client->request(
            'GET',
            '/api/1.2/version',
            [],
            [],
            ['HTTP_Accept' => 'application/json']
        );
        $this->assertEquals(
            '{"version":"1.2"}',
            $this->client->getResponse()->getContent()
        );
    }
}
