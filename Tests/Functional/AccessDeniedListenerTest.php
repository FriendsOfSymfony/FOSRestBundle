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

class AccessDeniedListenerTest extends WebTestCase
{
    private static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$client = static::createClient(['test_case' => 'AccessDeniedListener']);
    }

    public static function tearDownAfterClass()
    {
        self::deleteTmpDir('AccessDeniedListener');
        parent::tearDownAfterClass();
    }

    protected function setUp()
    {
        if (!interface_exists(ErrorRendererInterface::class)) {
            $this->markTestSkipped();
        }
    }

    public function testBundleListenerHandlesExceptionsInRestZonesWithoutLogin()
    {
        static::$client->request('GET', '/api/comments');

        $this->assertEquals(401, static::$client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', static::$client->getResponse()->headers->get('Content-Type'));
    }

    public function testBundleListenerHandlesExceptionsInRestZonesWithLogin()
    {
        $credentials = [
            'PHP_AUTH_USER' => 'restapi',
            'PHP_AUTH_PW' => 'secretpw',
        ];

        static::$client->request('GET', '/api/comments', [], [], $credentials);

        $this->assertEquals(200, static::$client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', static::$client->getResponse()->headers->get('Content-Type'));
    }

    public function testBundleListenerHandlesExceptionsInRestZonesWrongLogin()
    {
        $credentials = [
            'PHP_AUTH_USER' => 'admin',
            'PHP_AUTH_PW' => 'secretpw',
        ];

        static::$client->request('GET', '/api/comments', [], [], $credentials);

        $this->assertEquals(403, static::$client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', static::$client->getResponse()->headers->get('Content-Type'));
    }

    public function testBundleListenerHandlesExceptionsInRestZonesWithIncorrectLogin()
    {
        $credentials = [
            'PHP_AUTH_USER' => 'restapi',
            'PHP_AUTH_PW' => 'foobar',
        ];

        static::$client->request('GET', '/api/comments', [], [], $credentials);

        $this->assertEquals(401, static::$client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', static::$client->getResponse()->headers->get('Content-Type'));
    }

    public function testSymfonyListenerHandlesExceptionsOutsideRestZones()
    {
        static::$client->request('GET', '/admin/comments');

        $this->assertEquals(302, static::$client->getResponse()->getStatusCode());
        $this->assertEquals('text/html; charset=UTF-8', static::$client->getResponse()->headers->get('Content-Type'));
    }
}
