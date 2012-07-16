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

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use FOS\RestBundle\DependencyInjection\Compiler\ConfigurationCheckPass;
use JMS\SerializerBundle\DependencyInjection\JMSSerializerExtension;
use FOS\RestBundle\DependencyInjection\Factory\RoutingHandlerFactory;

/**
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Eriksen Costa <eriksencosta@gmail.com>
 */
class FOSRestBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigurationCheckPass());
    }

    /**
     * Returns a cleaned version number
     *
     * @param string $version
     *
     * @return string
     */
    public static function getSymfonyVersion($version)
    {
        return implode('.', array_slice(array_map(function($val) {
            return (int) $val;
        }, explode('.', $version)), 0, 3));
    }

    public function configureSerializerExtension(JMSSerializerExtension $ext)
    {
        $ext->addHandlerFactory(new RoutingHandlerFactory());
    }
}
