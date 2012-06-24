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
 * Checks if the:
 *  - SensioFrameworkExtraBundle views annotations are disabled when using 
 *    the View Response listener.
 *  - SensioFrameworkExtraBundle ParamConverter annotation are enabled when 
 *    using the Request Content Param Converter.
 * 
 * @author Eriksen Costa <eriksencosta@gmail.com>
 * @author Antoni Orfin <a.orfin@imagin.com.pl>
 */
class ConfigurationCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('sensio_framework_extra.view.listener') && $container->has('fos_rest.view_response_listener')) {
            throw new \RuntimeException('You need to disable the view annotations in SensioFrameworkExtraBundle when using the FOSRestBundle View Response listener.');
        }

        if ($container->has('fos_rest.request.param_converter.request_content') && !$container->has('sensio_framework_extra.converter.listener')) {
            throw new \RuntimeException('You need to enable the ParamConverter annotation in SensioFrameworkExtraBundle when using the FOSRestBundle Request Content Param Converter.');
        }
    }
}
