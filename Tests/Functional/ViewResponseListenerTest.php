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
