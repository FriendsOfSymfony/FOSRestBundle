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

    public static function setUpBeforeClass(): void
    {
        static::$client = static::createClient(['test_case' => 'Version']);
    }

    public static function tearDownAfterClass(): void
    {
        self::deleteTmpDir('Version');
    }

    public function testVersionAnnotation(): void
    {
        static::$client->request(
            'GET',
            '/version?query_version=1.2',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertEquals('{"version":"test annotation"}', static::$client->getResponse()->getContent());
    }

    public function testVersionInPathWithAnnotation(): void
    {
        static::$client->request(
            'GET',
            '/version/1.2',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertEquals(
            '{"version":"test annotation","version_exclusion":"1.2"}',
            static::$client->getResponse()->getContent()
        );
    }

    public function testCustomHeaderVersion(): void
    {
        static::$client->request(
            'GET',
            '/version?query_version=3.2',
            [],
            [],
            ['HTTP_Version-Header' => '2.1', 'HTTP_ACCEPT' => 'application/vnd.foo.api+json;myversion=2.3']
        );
        $this->assertEquals(
            '{"version":"2.1","version_exclusion":"2.1"}',
            static::$client->getResponse()->getContent()
        );
    }

    public function testQueryVersion(): void
    {
        static::$client->request(
            'GET',
            '/version?query_version=3.2',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertEquals('{"version":"3.2","version_exclusion":"3.2"}', static::$client->getResponse()->getContent());
    }

    public function testAcceptHeaderVersion(): void
    {
        static::$client->request(
            'GET',
            '/version?query_version=3.2',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/vnd.foo.api+json;myversion=2.3']
        );

        $response = static::$client->getResponse();
        $this->assertEquals('{"version":"2.3","version_exclusion":"2.3"}', $response->getContent());
        $this->assertEquals('application/vnd.foo.api+json;myversion=2.3', $response->headers->get('Content-Type'));
    }

    public function testDefaultVersion(): void
    {
        static::$client->request(
            'GET',
            '/version',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertEquals(
            '{"version":"3.4.2","version_exclusion":"3.4.2"}',
            static::$client->getResponse()->getContent()
        );
    }

    public function testVersionInPath(): void
    {
        static::$client->request(
            'GET',
            '/version/2.3',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );
        $this->assertEquals(
            '{"version":"2.3","version_exclusion":"2.3"}',
            static::$client->getResponse()->getContent()
        );
    }
}
