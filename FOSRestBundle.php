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

use Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\HttpKernel\Bundle\Bundle;

use FOS\RestBundle\DependencyInjection\Compiler\ConfigurationCheckPass;

/**
 * @author Lukas Kahwe Smith <smith@pooteeweet.org>
 * @author Eriksen Costa <eriksencosta@gmail.com>
 */
class FOSRestBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigurationCheckPass());
    }

    /**
     * Returns a cleaned version number
     *
     * @param string $version
     * @return string
     */
    public static function getSymfonyVersion($version)
    {
        return implode('.', array_slice(array_map(function($val)
        {
            return (int)$val;
        }, explode('.', $version)), 0, 3));
    }
}
