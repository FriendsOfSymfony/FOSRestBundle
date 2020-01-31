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
 * @group legacy
 */
class ExceptionListenerTest extends WebTestCase
{
    private static $client;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        static::$client = static::createClient(['test_case' => 'ExceptionListener']);
    }

    public static function tearDownAfterClass()
    {
        self::deleteTmpDir('ExceptionListener');
        parent::tearDownAfterClass();
    }

    public function testBundleListenerHandlesExceptionsInRestZones()
    {
        static::$client->request('GET', '/api/test');

        $this->assertEquals('application/json', static::$client->getResponse()->headers->get('Content-Type'));
    }

    public function testSymfonyListenerHandlesExceptionsOutsideRestZones()
    {
        static::$client->request('GET', '/test');

        $this->assertEquals('text/html; charset=UTF-8', static::$client->getResponse()->headers->get('Content-Type'));
    }
}
