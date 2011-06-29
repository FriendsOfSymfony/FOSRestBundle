<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;

use FOS\RestBundle\DependencyInjection\Configuration;

/**
 * FOSRestExtension test.
 *
 * @author Warnar Boekkooi <boekkooi>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test that the formats that are Set to False or NULL aren't loaded.
     */
    public function testFormatsLoad()
    {
        $configs = array(
            'fos_rest' => array(
                'formats' => array(
                    'xml' => '\SomeClass',
                    'html' => false,
                    'json' => null
                )
            )
        );

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $this->assertEquals(1, count($config['formats']));
        $this->assertArrayHasKey('xml', $config['formats']);
    }
}
