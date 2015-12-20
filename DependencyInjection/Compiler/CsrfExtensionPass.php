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

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * @internal
 */
class CsrfExtensionPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition('fos_rest.form.extension.csrf_disable')) {
            $definition = $container->getDefinition('fos_rest.form.extension.csrf_disable');

            if (interface_exists('Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface')) {
                $tokenStorageReference = new Reference('security.token_storage');
                $definition->addArgument(new Reference('security.authorization_checker'));
            } else {
                $tokenStorageReference = new Reference('security.context');
            }
            $definition->replaceArgument(0, $tokenStorageReference);
        }
    }
}
