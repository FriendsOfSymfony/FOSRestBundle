<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// BC layer for symfony < 5.3
if (class_exists(\Symfony\Component\HttpFoundation\Session\SessionFactory::class)) {
    $sessionConfig = [
        'storage_factory_id' => 'session.storage.factory.mock_file',
    ];
} else {
    $sessionConfig = [
        'storage_id' => 'session.storage.mock_file',
    ];
}

$frameworkConfig = [
    'annotations' => [
        'enabled' => true,
    ],
    'property_access' => null,
    'secret' => 'test',
    'router' => [
        'resource' => '%kernel.project_dir%/config/routing.yml',
        'utf8' => true,
    ],
    'test' => null,
    'csrf_protection' => null,
    'form' => null,
    'session' => $sessionConfig,
    'default_locale' => 'en',
];

if (\Symfony\Component\HttpKernel\Kernel::VERSION_ID >= 60100) {
    $frameworkConfig['http_method_override'] = true;
}

if (\Symfony\Component\HttpKernel\Kernel::VERSION_ID >= 70000) {
    unset($frameworkConfig['annotations']);
}

$container->loadFromExtension('framework', $frameworkConfig);
