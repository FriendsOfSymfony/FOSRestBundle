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

class ExceptionListenerTest extends WebTestCase
{
    private $client;

    public function setUp()
    {
        $this->client = $this->createClient(['test_case' => 'ExceptionListener']);
    }

    public function testBundleListenerHandlesExceptionsInRestZones()
    {
        $this->client->request('GET', '/api/test');

        $this->assertEquals('application/json', $this->client->getResponse()->headers->get('Content-Type'));
    }

    public function testSymfonyListenerHandlesExceptionsOutsideRestZones()
    {
        $this->client->request('GET', '/test');

        $this->assertEquals('text/html; charset=UTF-8', $this->client->getResponse()->headers->get('Content-Type'));
    }
}
