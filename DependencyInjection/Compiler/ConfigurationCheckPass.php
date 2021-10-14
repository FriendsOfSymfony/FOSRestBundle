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
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;

/**
 * Checks if the SensioFrameworkExtraBundle views annotations are disabled when using the View Response listener.
 *
 * @author Eriksen Costa <eriksencosta@gmail.com>
 *
 * @internal
 */
final class ConfigurationCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if ($container->has('fos_rest.converter.request_body') && !($container->has('sensio_framework_extra.converter.listener') || $container->has(ParamConverterListener::class))) {
            throw new \RuntimeException('You need to enable the parameter converter listeners in SensioFrameworkExtraBundle when using the FOSRestBundle RequestBodyParamConverter');
        }
    }
}
