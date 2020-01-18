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
class LegacyTemplatingTest extends WebTestCase
{
    public static function tearDownAfterClass()
    {
        self::deleteTmpDir('LegacyTemplating');
        parent::tearDownAfterClass();
    }

    public function testSerializeExceptionHtml()
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => 'LegacyTemplating', 'debug' => false]);
        $client->request('GET', '/serializer-error/exception.html');

        $this->assertContains('The server returned a "500 Internal Server Error".', $client->getResponse()->getContent());
        $this->assertNotContains('Something bad happened', $client->getResponse()->getContent());
    }

    public function testSerializeExceptionHtmlInDebugMode()
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => 'LegacyTemplating', 'debug' => true]);
        $client->request('GET', '/serializer-error/exception.html');

        $this->assertContains('Something bad happened. (500 Internal Server Error)', $client->getResponse()->getContent());
    }

    public function testTemplateOverride()
    {
        $client = $this->createClient(array('test_case' => 'LegacyTemplating'));
        $client->request(
            'GET',
            '/articles'
        );

        $this->assertSame(200, $client->getResponse()->getStatusCode(), $client->getResponse()->getContent());
        $this->assertContains('fooo', $client->getResponse()->getContent());
    }
}
