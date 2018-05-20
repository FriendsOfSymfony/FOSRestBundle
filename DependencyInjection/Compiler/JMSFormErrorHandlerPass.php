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
use FOS\RestBundle\Serializer\Normalizer\FormErrorHandler;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Decorates the JMS FormErrorHandler.
 *
 * @author Guilhem Niot <guilhem.niot@gmail.com>
 * @author Christian Flothmann <christian.flothmann@sensiolabs.de>
 *
 * @internal
 */
final class JMSFormErrorHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('jms_serializer.form_error_handler')) {
            return;
        }

        $container->register('fos_rest.serializer.form_error_handler', FormErrorHandler::class)
            ->setDecoratedService('jms_serializer.form_error_handler')
            ->addArgument(new Reference('fos_rest.serializer.form_error_handler.inner'));
    }
}
