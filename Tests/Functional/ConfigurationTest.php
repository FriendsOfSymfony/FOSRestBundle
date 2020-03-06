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
class ConfigurationTest extends WebTestCase
{
    public function testDisabledTemplating()
    {
        $kernel = self::bootKernel(['test_case' => 'Configuration']);
        $container = $kernel->getContainer();

        $this->assertFalse($container->has('fos_rest.templating'));
    }

    public function testEnabledTemplatingWithTwig()
    {
        $kernel = self::bootKernel(['test_case' => 'ConfigurationWithTwig']);
        $container = $kernel->getContainer();

        $this->assertTrue($container->has('fos_rest.templating'));
    }

    public function testToolbar()
    {
        $client = $this->createClient(['test_case' => 'ConfigurationWithTwig']);
        $client->request(
            'GET',
            '/_profiler/empty/search/results?limit=10',
            [],
            [],
            ['HTTP_Accept' => 'application/xml']
        );

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertSame('text/html; charset=UTF-8', $client->getResponse()->headers->get('Content-Type'));
    }
}
