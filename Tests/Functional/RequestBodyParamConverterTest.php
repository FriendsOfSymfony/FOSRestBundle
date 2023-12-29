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

use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;

class RequestBodyParamConverterTest extends WebTestCase
{
    public function testRequestBodyIsDeserialized()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

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

    public function testErrorPageServedByFrameworkBundle()
    {
        if (!class_exists(SensioFrameworkExtraBundle::class)) {
            $this->markTestSkipped('Test requires sensio/framework-extra-bundle');
        }

        $client = $this->createClient(['test_case' => 'RequestBodyParamConverterFrameworkBundle']);
        $client->request('GET', '/_error/404.txt');

        $this->assertEquals(404, $client->getResponse()->getStatusCode());
        $this->assertStringContainsString('The server returned a "404 Not Found".', $client->getResponse()->getContent());
    }
}
