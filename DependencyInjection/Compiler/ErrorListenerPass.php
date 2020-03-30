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

/**
 * Handle the error error related listener.
 *
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 *
 * @internal
 */
final class ErrorListenerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('fos_rest.exception_listener')) {
            return;
        }

        if ($container->has('twig.exception_listener')) {
            $container->getDefinition('fos_rest.exception_listener')->setDecoratedService('twig.exception_listener');
        } elseif ($container->has('exception_listener')) {
            $container->getDefinition('fos_rest.exception_listener')->setDecoratedService('exception_listener');
        }
    }
}
