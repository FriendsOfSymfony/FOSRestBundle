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
 * Checks if the JMS serializer is available to be able to use the ExceptionWrapperSerializeHandler.
 *
 * @author Christian Flothmann <christian.flothmann@xabbuh.de>
 */
class ExceptionWrapperHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('fos_rest.serializer.exception_wrapper_serialize_handler')) {
            return;
        }

        if (interface_exists('JMS\Serializer\Handler\SubscribingHandlerInterface')) {
            return;
        }

        $container->removeDefinition('fos_rest.serializer.exception_wrapper_serialize_handler');
    }
}
