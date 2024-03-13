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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * ConfigurationCheckPass test.
 *
 * @author Eriksen Costa <eriksencosta@gmail.com>
 */
class ConfigurationCheckPassTest extends TestCase
{
    public function testShouldThrowRuntimeExceptionWhenBodyConverterIsEnabledButParamConvertersAreNotEnabled(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('You need to enable the parameter converter listeners in SensioFrameworkExtraBundle when using the FOSRestBundle RequestBodyParamConverter');

        $container = new ContainerBuilder();

        $container->register('fos_rest.converter.request_body');

        $compiler = new ConfigurationCheckPass();
        $compiler->process($container);
    }
}
