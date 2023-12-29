<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$frameworkConfig = [
    'annotations' => [
        'enabled' => true,
    ],
    'property_access' => null,
    'serializer' => [
        'enabled' => true,
    ],
    'router' => [
        'resource' => '%kernel.project_dir%/BasicAuth/routing.yml',
    ],
    'profiler' => [
        'only_exceptions' => false,
    ],
];

if (\Symfony\Component\HttpKernel\Kernel::VERSION_ID >= 60100) {
    $frameworkConfig['http_method_override'] = true;
}

if (\Symfony\Component\HttpKernel\Kernel::VERSION_ID >= 70000) {
    unset($frameworkConfig['annotations']);
}

$container->loadFromExtension('framework', $frameworkConfig);
