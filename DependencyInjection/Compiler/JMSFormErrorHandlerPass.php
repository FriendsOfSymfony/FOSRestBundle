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

/**
 * Changes the JMS FormError handler.
 *
 * @author Guilhem Niot <guilhem.niot@gmail.com>
 *
 * @internal
 */
final class JMSFormErrorHandlerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $container->setParameter('jms_serializer.form_error_handler.class', FormErrorHandler::class);
    }
}
