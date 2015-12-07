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
 * Checks if the SensioFrameworkExtraBundle views annotations are disabled when using the View Response listener.
 *
 * @author Eriksen Costa <eriksencosta@gmail.com>
 *
 * @internal
 */
class ConfigurationCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('sensio_framework_extra.view.listener') && $container->has('fos_rest.view_response_listener')) {
            throw new \RuntimeException('You need to disable the view annotations in SensioFrameworkExtraBundle when using the FOSRestBundle View Response listener. Add "view: { annotations: false }" to the sensio_framework_extra: section of your config.yml');
        }

        if ($container->has('fos_rest.converter.request_body') && !$container->has('sensio_framework_extra.converter.listener')) {
            throw new \RuntimeException('You need to enable the parameter converter listeners in SensioFrameworkExtraBundle when using the FOSRestBundle RequestBodyParamConverter');
        }
    }
}
