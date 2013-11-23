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
use Symfony\Component\DependencyInjection\Reference;

/**
 * Enable version feature if JMSSerializer bundle if present
 *
 * @author Jérémy Leherpeur <jeremy@leherpeur.net>
 */
class VersionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (class_exists('JMS\SerializerBundle\JMSSerializerBundle')) {
            $container
                ->register('fos_rest.version.serialisation_context', 'JMS\Serializer\SerializationContext');

            $container
                ->register('fos_rest.version.listener', 'FOS\RestBundle\EventListener\VersionListener')
                ->addArgument(new Reference('fos_rest.version.serialisation_context'))
                ->addArgument(new Reference('annotation_reader'))
                ->addTag('kernel.event_listener', array(
                    'event' => 'kernel.request',
                    'method' => 'onKernelRequest'
                ))
                ->addTag('kernel.event_listener', array(
                    'event' => 'kernel.controller',
                    'method' => 'onKernelController',
                    'priority' => -255
                ))
            ;
        }
    }
}