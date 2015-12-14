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
    private $client;

    public function setUp()
    {
        $this->client = $this->createClient(array('test_case' => 'Version'));
    }

    public function testCustomHeaderVersion()
    {
        $this->client->request(
            'GET',
            '/version?query_version=3.2',
            array(),
            array(),
            array('HTTP_Version-Header' => 2.1, 'HTTP_Accept' => 'application/json;myversion=2.3')
        );
        $this->assertEquals('2.1', $this->client->getResponse()->getContent());
    }

    public function testQueryVersion()
    {
        $this->client->request(
            'GET',
            '/version?query_version=3.2'
        );
        $this->assertEquals('3.2', $this->client->getResponse()->getContent());
    }

    public function testAcceptHeaderVersion()
    {
        $this->client->request(
            'GET',
            '/version?query_version=3.2',
            array(),
            array(),
            array('HTTP_Accept' => 'application/json;myversion=2.3')
        );
        $this->assertEquals('2.3', $this->client->getResponse()->getContent());
    }

    public function testDefaultVersion()
    {
        $this->client->request(
            'GET',
            '/version'
        );
        $this->assertEquals('3.4.2', $this->client->getResponse()->getContent());
    }
}
