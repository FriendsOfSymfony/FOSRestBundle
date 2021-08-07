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

use FOS\RestBundle\Serializer\JMSHandlerRegistryV2;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Decorates the handler registry from JMSSerializerBundle.
 *
 * It works as HandlerRegistryDecorationPass but uses the symfony built-in decoration mechanism.
 * This way of decoration is possible only starting from jms/serializer-bundle:4.0 .
 *
 * @author Asmir Mustafic <goetas@gmail.com>
 *
 * @internal
 */
class JMSHandlerRegistryV4DecorationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        // skip if jms/serializer-bundle is not installed or < 4.0
        if (!$container->has('jms_serializer.handler_registry') || !$container->has('jms_serializer.handler_registry.service_locator')) {
            return;
        }

        $fosRestHandlerRegistry = new Definition(
            JMSHandlerRegistryV2::class,
            [
                new Reference('fos_rest.serializer.jms_handler_registry.inner'),
            ]
        );

        $fosRestHandlerRegistry->setDecoratedService('jms_serializer.handler_registry');
        $container->setDefinition('fos_rest.serializer.jms_handler_registry', $fosRestHandlerRegistry);
    }
}
