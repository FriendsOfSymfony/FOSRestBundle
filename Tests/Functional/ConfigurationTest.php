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
}
