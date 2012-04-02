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

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Checks if the SensioFrameworkExtraBundle views annotations are disabled when using the View Response listener.
 *
 * @author Eriksen Costa <eriksencosta@gmail.com>
 */
class ConfigurationCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('sensio_framework_extra.view.listener') && $container->has('fos_rest.view_response_listener')) {
            throw new \RuntimeException('You need to disable the view annotations in SensioFrameworkExtraBundle when using the FOSRestBundle View Response listener.');
        }
    }
}
