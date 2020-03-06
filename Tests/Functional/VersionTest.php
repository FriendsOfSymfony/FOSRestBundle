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
class VersionTest extends WebTestCase
{
    private static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$client = static::createClient(['test_case' => 'Version']);
    }

    public static function tearDownAfterClass()
    {
        self::deleteTmpDir('Version');
        parent::tearDownAfterClass();
    }

    public function testVersionAnnotation()
    {
        static::$client->request(
            'GET',
            '/version?query_version=1.2',
            [],
            [],
            ['HTTP_Accept' => 'application/json']
        );
        $this->assertEquals('{"version":"test annotation"}', static::$client->getResponse()->getContent());
    }

    public function testVersionInPathWithAnnotation()
    {
        static::$client->request(
            'GET',
            '/version/1.2',
            [],
            [],
            ['HTTP_Accept' => 'application/json']
        );
        $this->assertEquals(
            '{"version":"test annotation","version_exclusion":"1.2"}',
            static::$client->getResponse()->getContent()
        );
    }

    public function testCustomHeaderVersion()
    {
        static::$client->request(
            'GET',
            '/version?query_version=3.2',
            [],
            [],
            ['HTTP_Version-Header' => '2.1', 'HTTP_Accept' => 'application/vnd.foo.api+json;myversion=2.3']
        );
        $this->assertEquals(
            '{"version":"2.1","version_exclusion":"2.1"}',
            static::$client->getResponse()->getContent()
        );
    }

    public function testQueryVersion()
    {
        static::$client->request(
            'GET',
            '/version?query_version=3.2',
            [],
            [],
            ['HTTP_Accept' => 'application/json']
        );
        $this->assertEquals('{"version":"3.2","version_exclusion":"3.2"}', static::$client->getResponse()->getContent());
    }

    public function testAcceptHeaderVersion()
    {
        static::$client->request(
            'GET',
            '/version?query_version=3.2',
            [],
            [],
            ['HTTP_Accept' => 'application/vnd.foo.api+json;myversion=2.3']
        );

        $response = static::$client->getResponse();
        $this->assertEquals('{"version":"2.3","version_exclusion":"2.3"}', $response->getContent());
        $this->assertEquals('application/vnd.foo.api+json;myversion=2.3', $response->headers->get('Content-Type'));
    }

    public function testDefaultVersion()
    {
        static::$client->request(
            'GET',
            '/version',
            [],
            [],
            ['HTTP_Accept' => 'application/json']
        );
        $this->assertEquals(
            '{"version":"3.4.2","version_exclusion":"3.4.2"}',
            static::$client->getResponse()->getContent()
        );
    }

    public function testVersionInPath()
    {
        static::$client->request(
            'GET',
            '/version/2.3',
            [],
            [],
            ['HTTP_Accept' => 'application/json']
        );
        $this->assertEquals(
            '{"version":"2.3","version_exclusion":"2.3"}',
            static::$client->getResponse()->getContent()
        );
    }
}
