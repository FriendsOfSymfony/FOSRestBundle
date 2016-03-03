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

class RequestBodyParamConverterTest extends WebTestCase
{
    public function testRequestBodyIsDeserialized()
    {
        $client = $this->createClient(['test_case' => 'RequestBodyParamConverter']);
        $client->request(
            'POST',
            '/body-converter',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name": "Post 1", "body": "This is a blog post"}'
        );

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Post 1', $client->getResponse()->getContent());
    }

    /**
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/issues/1237
     */
    public function testTwigErrorPage()
    {
        $client = $this->createClient(['test_case' => 'RequestBodyParamConverter']);
        $client->request('GET', '/_error/404.txt');

        // Status code 200 as this page describes an error but is not the result of an error.
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('The server returned a "404 Not Found".', $client->getResponse()->getContent());
    }
}
