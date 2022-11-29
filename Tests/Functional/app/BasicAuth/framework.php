<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;

return static function (ContainerConfigurator $container) {
    $config = [
        'serializer' => [
            'enabled' => true,
        ],
        'router' => [
            'resource' => '%kernel.project_dir%/BasicAuth/routing.yml',
        ],
    ];

    if (Kernel::VERSION_ID >= 60100) {
        $config['http_method_override'] = true;
    }

    $container->loadFromExtension('framework', $config);
};
