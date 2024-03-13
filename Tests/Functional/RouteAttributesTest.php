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
 * @requires PHP 8
 */
class RouteAttributesTest extends WebTestCase
{
    private const TEST_CASE = 'RouteAttributes';
    private static $client;

    public static function setUpBeforeClass(): void
    {
        static::$client = static::createClient(['test_case' => self::TEST_CASE]);
    }

    public static function tearDownAfterClass(): void
    {
        self::deleteTmpDir(self::TEST_CASE);
    }

    public function testGet(): void
    {
        static::$client->request(
            'GET',
            '/products/1',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertSame(200, static::$client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '[{"name": "product1"},{"name": "product2"}]',
            static::$client->getResponse()->getContent()
        );
    }

    public function testPost(): void
    {
        static::$client->request(
            'POST',
            '/products',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertSame(201, static::$client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"name": "product1"}',
            static::$client->getResponse()->getContent()
        );
    }

    public function testInvalidQueryParameter(): void
    {
        static::$client->request(
            'GET',
            '/products/foo',
            [],
            [],
            ['HTTP_ACCEPT' => 'application/json']
        );

        $this->assertSame(404, static::$client->getResponse()->getStatusCode());
    }
}
