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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Checks if a serializer is either set or can be auto-configured.
 *
 * @author Christian Flothmann <christian.flothmann@xabbuh.de>
 * @author Florian Voutzinos <florian@voutzinos.com>
 */
class SerializerConfigurationPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('fos_rest.serializer')) {
            return;
        }

        if ($container->has('jms_serializer.serializer')) {
            $container->setAlias('fos_rest.serializer', 'jms_serializer.serializer');
            $container->removeDefinition('fos_rest.serializer.exception_wrapper_normalizer');
        } elseif ($container->has('serializer')) {
            $container->setAlias('fos_rest.serializer', 'serializer');
            $container->removeDefinition('fos_rest.serializer.exception_wrapper_serialize_handler');
        } else {
            throw new \InvalidArgumentException('Neither a service called "jms_serializer.serializer" nor "serializer" is available and no serializer is explicitly configured. You must either enable the JMSSerializerBundle, enable the FrameworkBundle serializer or configure a custom serializer.');
        }
    }
}
