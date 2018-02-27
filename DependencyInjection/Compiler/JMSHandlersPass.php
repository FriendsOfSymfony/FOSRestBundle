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

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Checks if the JMS serializer is available to be able to use handlers.
 *
 * @author Christian Flothmann <christian.flothmann@xabbuh.de>
 *
 * @internal
 */
final class JMSHandlersPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('jms_serializer.handler_registry')) {
            // the public alias prevents the handler registry definition from being removed
            $container->setAlias('fos_rest.serializer.jms_handler_registry', new Alias('jms_serializer.handler_registry', true));

            return;
        }

        $container->removeDefinition('fos_rest.serializer.handler_registry');
        $container->removeDefinition('fos_rest.serializer.exception_normalizer.jms');
        $container->getParameterBag()->remove('jms_serializer.form_error_handler.class');
    }
}
