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

class RoutingTest extends WebTestCase
{
    private static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$client = static::createClient(['test_case' => 'Routing']);
    }

    public static function tearDownAfterClass()
    {
        self::deleteTmpDir('Routing');
        parent::tearDownAfterClass();
    }

    public function testPostControllerRoutesAreRegistered()
    {
        static::$client->request('GET', '/posts/1');

        $this->assertSame(200, static::$client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{ "id": 1 }', static::$client->getResponse()->getContent());
    }

    public function testCommentControllerRoutesAreRegistered()
    {
        static::$client->request('GET', '/comments/3');

        $this->assertSame(200, static::$client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{ "id": 3 }', static::$client->getResponse()->getContent());
    }
}
