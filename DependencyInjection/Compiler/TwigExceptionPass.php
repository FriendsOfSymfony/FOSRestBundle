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
use Symfony\Component\HttpKernel\Kernel;

/**
 * Remove the 'fos_rest.exception.twig_controller' service if templating is not enabled and configure default exception controller.
 *
 * @internal
 */
final class TwigExceptionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // when no custom exception controller has been set
        if ($container->hasDefinition('fos_rest.exception_listener') &&
            null === $container->getDefinition('fos_rest.exception_listener')->getArgument(0)
        ) {
            if (isset($container->getParameter('kernel.bundles')['TwigBundle']) && ($container->has('templating.engine.twig') || $container->has('twig'))) {
                // only use this when TwigBundle is enabled and the deprecated SF templating integration is used
                $controller = Kernel::VERSION_ID >= 40100 ? 'fos_rest.exception.twig_controller::showAction' : 'fos_rest.exception.twig_controller:showAction';
            } else {
                $controller = Kernel::VERSION_ID >= 40100 ? 'fos_rest.exception.controller::showAction' : 'fos_rest.exception.controller:showAction';
            }

            $container->getDefinition('fos_rest.exception_listener')->replaceArgument(0, $controller);
        }

        if (!$container->has('templating.engine.twig')) {
            if ($container->has('twig') && $container->has('fos_rest.exception.twig_controller')) {
                $container->findDefinition('fos_rest.exception.twig_controller')->replaceArgument(3, $container->findDefinition('twig'));
            } else {
                $container->removeDefinition('fos_rest.exception.twig_controller');
            }
        }
    }
}
