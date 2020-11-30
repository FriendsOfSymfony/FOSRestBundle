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

use Symfony\Component\ErrorHandler\ErrorRenderer\ErrorRendererInterface;

/**
 * @group legacy
 */
class AccessDeniedListenerTest extends WebTestCase
{
    private static $client;

    public static function setUpBeforeClass(): void
    {
        if (!interface_exists(ErrorRendererInterface::class)) {
            self::markTestSkipped();
        }

        parent::setUpBeforeClass();
        static::$client = static::createClient(['test_case' => 'AccessDeniedListener']);
    }

    public static function tearDownAfterClass(): void
    {
        self::deleteTmpDir('AccessDeniedListener');
        parent::tearDownAfterClass();
    }

    public function testNoCredentialsGives403()
    {
        static::$client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json']);
        $response = static::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testWrongLoginGives401()
    {
        static::$client->request('POST', '/api/login', [], [], ['HTTP_X-FOO' => 'BAR', 'CONTENT_TYPE' => 'application/json']);
        $response = static::$client->getResponse();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testSuccessfulLogin()
    {
        static::$client->request('POST', '/api/login', [], [], ['HTTP_X-FOO' => 'FOOBAR', 'CONTENT_TYPE' => 'application/json']);
        $response = static::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testAccessDeniedExceptionGives403()
    {
        static::$client->request('GET', '/api/comments', [], [], ['CONTENT_TYPE' => 'application/json']);
        $response = static::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }
}
