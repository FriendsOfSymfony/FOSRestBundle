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

abstract class AbstractAuthenticatorTestCase extends WebTestCase
{
    protected static $client;

    public static function setUpBeforeClass(): void
    {
        if (!interface_exists(ErrorRendererInterface::class)) {
            self::markTestSkipped();
        }

        self::$client = self::createClient(['test_case' => static::getTestCase()]);
    }

    public static function tearDownAfterClass(): void
    {
        self::deleteTmpDir(static::getTestCase());
    }

    public function testNoCredentialsGives401(): void
    {
        self::$client->request('POST', '/api/login', [], [], ['CONTENT_TYPE' => 'application/json']);
        $response = self::$client->getResponse();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testWrongCredentialsGives401(): void
    {
        $this->sendRequestContainingInvalidCredentials('/api/login');

        $response = self::$client->getResponse();

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testSuccessfulLogin(): void
    {
        $this->sendRequestContainingValidCredentials('/api/login');

        $response = self::$client->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    public function testAccessDeniedExceptionGives403(): void
    {
        $this->sendRequestContainingValidCredentials('/api/comments');

        $response = self::$client->getResponse();

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('Content-Type'));
    }

    abstract protected static function getTestCase(): string;

    abstract protected function sendRequestContainingInvalidCredentials(string $path): void;

    abstract protected function sendRequestContainingValidCredentials(string $path): void;
}
