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
 * Remove the 'twig.exception_listener' service if 'fos_rest.exception_listener' is activated.
 */
class TwigExceptionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('fos_rest.exception_listener') && $container->has('twig.exception_listener')) {
            $container->removeDefinition('twig.exception_listener');
        }
    }
}
