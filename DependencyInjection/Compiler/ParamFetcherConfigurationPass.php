<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * BC for symfony 2.3.
 *
 * @author Ener-Getick <egetick@gmail.com>
 *
 * @internal
 */
class ParamFetcherConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_rest.request.param_fetcher') || $container->hasDefinition('request_stack')) {
            return;
        }

        $definition = $container->getDefinition('fos_rest.request.param_fetcher');
        $definition->addMethodCall('setContainer', array(new Reference('service_container')));
    }
}
