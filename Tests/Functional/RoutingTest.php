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
    private $client;

    public function setUp()
    {
        $this->client = $this->createClient(array('test_case' => 'Routing'));
    }

    public function testPostControllerRoutesAreRegistered()
    {
        $this->client->request('GET', '/posts/1');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{ "id": 1 }', $this->client->getResponse()->getContent());
    }

    public function testCommentControllerRoutesAreRegistered()
    {
        $this->client->request('GET', '/comments/3');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString('{ "id": 3 }', $this->client->getResponse()->getContent());
    }
}
