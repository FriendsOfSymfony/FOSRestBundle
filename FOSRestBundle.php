<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle;

use FOS\RestBundle\DependencyInjection\Compiler\ConfigurationCheckPass;
use FOS\RestBundle\DependencyInjection\Compiler\HandlerRegistryDecorationPass;
use FOS\RestBundle\DependencyInjection\Compiler\JMSFormErrorHandlerPass;
use FOS\RestBundle\DependencyInjection\Compiler\JMSHandlersPass;
use FOS\RestBundle\DependencyInjection\Compiler\FormatListenerRulesPass;
use FOS\RestBundle\DependencyInjection\Compiler\SerializerConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Eriksen Costa <eriksencosta@gmail.com>
 */
class FOSRestBundle extends Bundle
{
    const ZONE_ATTRIBUTE = '_fos_rest_zone';

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SerializerConfigurationPass());
        $container->addCompilerPass(new ConfigurationCheckPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, -10);
        $container->addCompilerPass(new FormatListenerRulesPass());
        $container->addCompilerPass(new JMSFormErrorHandlerPass());
        $container->addCompilerPass(new JMSHandlersPass(), PassConfig::TYPE_BEFORE_REMOVING, -10);
        $container->addCompilerPass(new HandlerRegistryDecorationPass(), PassConfig::TYPE_AFTER_REMOVING);
    }
}
