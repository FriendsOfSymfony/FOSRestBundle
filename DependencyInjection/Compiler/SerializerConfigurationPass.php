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

use FOS\RestBundle\Serializer\Serializer;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Checks if a serializer is either set or can be auto-configured.
 *
 * @author Christian Flothmann <christian.flothmann@xabbuh.de>
 * @author Florian Voutzinos <florian@voutzinos.com>
 *
 * @internal
 */
final class SerializerConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('fos_rest.serializer')) {
            $class = $container->getParameterBag()->resolveValue(
                $container->findDefinition('fos_rest.serializer')->getClass()
            );
            if (!is_subclass_of($class, Serializer::class)) {
                throw new \InvalidArgumentException(sprintf('"fos_rest.serializer" must implement %s (instance of "%s" given).', Serializer::class, $class));
            }

            return;
        }

        if (!$container->has('serializer') && !$container->has('jms_serializer.serializer')) {
            throw new \InvalidArgumentException('Neither a service called "jms_serializer.serializer" nor "serializer" is available and no serializer is explicitly configured. You must either enable the JMSSerializerBundle, enable the FrameworkBundle serializer or configure a custom serializer.');
        }

        if ($container->has('jms_serializer.serializer')) {
            $container->setAlias('fos_rest.serializer', 'fos_rest.serializer.jms');

            return;
        }

        // As there is no `jms_serializer.serializer` service, there is a `serializer` service
        $class = $container->getParameterBag()->resolveValue(
            $container->findDefinition('serializer')->getClass()
        );

        if (is_subclass_of($class, SerializerInterface::class)) {
            $container->setAlias('fos_rest.serializer', 'fos_rest.serializer.symfony');
        } elseif (is_subclass_of($class, Serializer::class)) {
            $container->setAlias('fos_rest.serializer', 'serializer');
        } else {
            throw new \InvalidArgumentException(sprintf('The class of the "serializer" service in use is not supported (instance of "%s" given). Please make it implement %s or configure the service "fos_rest.serializer" with a class implementing %s.', $class, Serializer::class, Serializer::class));
        }
    }
}
