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
class ErrorWithTemplatingFormatTest extends WebTestCase
{
    public function testSerializeExceptionHtml()
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => 'Serializer', 'debug' => false]);
        $client->request('GET', '/serializer-error/exception.html');

        $this->assertContains('The server returned a "500 Internal Server Error".', $client->getResponse()->getContent());
        $this->assertNotContains('Something bad happened', $client->getResponse()->getContent());
    }

    public function testSerializeExceptionHtmlInDebugMode()
    {
        $this->iniSet('error_log', file_exists('/dev/null') ? '/dev/null' : 'nul');

        $client = $this->createClient(['test_case' => 'Serializer', 'debug' => true]);
        $client->request('GET', '/serializer-error/exception.html');

        $this->assertContains('Something bad happened. (500 Internal Server Error)', $client->getResponse()->getContent());
    }
}
