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
        $client = $this->createClient(array('test_case' => 'RequestBodyParamConverter'));
        $client->request(
            'POST',
            '/body-converter',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name": "Post 1", "body": "This is a blog post"}'
        );

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('Post 1', $client->getResponse()->getContent());
    }
}
