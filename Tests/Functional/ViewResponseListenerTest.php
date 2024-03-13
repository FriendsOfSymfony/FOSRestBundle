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

class ViewResponseListenerTest extends WebTestCase
{
    public static function tearDownAfterClass(): void
    {
        self::deleteTmpDir('ViewResponseListener');
    }

    public function testRedirect(): void
    {
        $client = $this->createClient(['test_case' => 'ViewResponseListener']);
        $client->request(
            'POST',
            '/articles.json',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            '{"name": "Post 1", "body": "This is a blog post"}'
        );

        $this->assertSame(201, $client->getResponse()->getStatusCode());
        $this->assertSame('http://localhost/hello/Post%201', $client->getResponse()->headers->get('location'));
        $this->assertStringNotContainsString('fooo', $client->getResponse()->getContent());
    }
}
