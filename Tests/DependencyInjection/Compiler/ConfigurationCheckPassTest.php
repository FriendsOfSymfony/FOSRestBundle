<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\Tests\DependencyInjection\Compiler;

use FOS\RestBundle\DependencyInjection\Compiler\ConfigurationCheckPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * ConfigurationCheckPass test.
 *
 * @author Eriksen Costa <eriksencosta@gmail.com>
 */
class ConfigurationCheckPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage You need to enable the parameter converter listeners in SensioFrameworkExtraBundle when using the FOSRestBundle RequestBodyParamConverter
     */
    public function testShouldThrowRuntimeExceptionWhenBodyConverterIsEnabledButParamConvertersAreNotEnabled()
    {
        $container = new ContainerBuilder();

        $container->register('fos_rest.converter.request_body');

        $compiler = new ConfigurationCheckPass();
        $compiler->process($container);
    }

    /**
     * @expectedException \RuntimeException
     * @expectedExceptionMessage SensioFrameworkExtraBundle view annotations
     */
    public function testExceptionWhenViewAnnotationsAreNotEnabled()
    {
        $container = new ContainerBuilder();

        $container->register('fos_rest.view_response_listener');
        $container->setParameter('kernel.bundles', ['SensioFrameworkExtraBundle' => '']);

        $compiler = new ConfigurationCheckPass();
        $compiler->process($container);
    }
}
