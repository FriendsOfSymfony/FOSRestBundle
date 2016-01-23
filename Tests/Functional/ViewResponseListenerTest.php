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
    public function testRedirect()
    {
        $client = $this->createClient(array('test_case' => 'ViewResponseListener'));
        $client->request(
            'POST',
            '/articles',
            array(),
            array(),
            array('CONTENT_TYPE' => 'application/json'),
            '{"name": "Post 1", "body": "This is a blog post"}'
        );

        $this->assertSame(302, $client->getResponse()->getStatusCode());
        $this->assertSame('http://localhost/hello/Post%201', $client->getResponse()->headers->get('location'));
        $this->assertNotContains('fooo', $client->getResponse()->getContent());
    }

    public function testTemplateOverride()
    {
        $client = $this->createClient(array('test_case' => 'ViewResponseListener'));
        $client->request(
            'GET',
            '/articles'
        );

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('fooo', $client->getResponse()->getContent());
    }
}
