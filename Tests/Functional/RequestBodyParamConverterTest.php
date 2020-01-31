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

use Symfony\Bundle\TwigBundle\Controller\PreviewErrorController;

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
     * Added to the legacy group to not trigger a deprecation. This deprecation is triggered on version 4.4 of
     * the TwigBundle where the PreviewErrorController class is deprecated. Since we only make sure not to break
     * that controller class, we do not have to care about the deprecations.
     *
     * @group legacy
     *
     * @see https://github.com/FriendsOfSymfony/FOSRestBundle/issues/1237
     */
    public function testTwigErrorPage()
    {
        if (!class_exists(PreviewErrorController::class)) {
            $this->markTestSkipped();
        }

        $client = $this->createClient(['test_case' => 'RequestBodyParamConverter']);
        $client->request('GET', '/_error/404.txt');

        // Status code 200 as this page describes an error but is not the result of an error.
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains('The server returned a "404 Not Found".', $client->getResponse()->getContent());
    }
}
