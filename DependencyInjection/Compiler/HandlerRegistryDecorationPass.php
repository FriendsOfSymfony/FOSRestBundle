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

use FOS\RestBundle\Serializer\JMSHandlerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Decorates the handler registry from JMSSerializerBundle.
 *
 * The logic is borrowed from the core Symfony DecoratorServicePass, but is implemented here to respect the fact that
 * custom handlers are registered in JMSSerializerBundle in a compiler pass that is executed after decorated services
 * have been resolved.
 *
 * @author Christian Flothmann <christian.flothmann@sensiolabs.de>
 */
class HandlerRegistryDecorationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('fos_rest.serializer.jms_handler_registry')) {
            return;
        }

        $jmsHandlerRegistry = $container->findDefinition('fos_rest.serializer.jms_handler_registry');
        $public = $jmsHandlerRegistry->isPublic();
        $jmsHandlerRegistry->setPublic(false);
        $container->setDefinition('fos_rest.serializer.jms_handler_registry.inner', $jmsHandlerRegistry);

        $fosRestHandlerRegistry = $container->register('jms_serializer.handler_registry', JMSHandlerRegistry::class)
            ->setPublic($public)
            ->addArgument(new Reference('fos_rest.serializer.jms_handler_registry.inner'));

        // remap existing aliases (they have already been replaced with the actual definition by Symfony's ReplaceAliasByActualDefinitionPass)
        foreach ($container->getDefinitions() as $id => $definition) {
            if ('fos_rest.serializer.jms_handler_registry.inner' !== $id && $definition === $jmsHandlerRegistry) {
                $container->setDefinition($id, $fosRestHandlerRegistry);
            }
        }
    }
}
