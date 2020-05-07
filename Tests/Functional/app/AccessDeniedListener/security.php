<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$container->loadFromExtension('security', [
    'encoders' => ['Symfony\Component\Security\Core\User\User' => 'plaintext'],
    'providers' => [
        'in_memory' => [
            'memory' => [
                'users' => [
                    'restapi' => ['password' => 'secretpw', 'roles' => ['ROLE_API']],
                    'admin' => ['password' => 'secretpw', 'roles' => ['ROLE_ADMIN']],
                ],
            ],
        ],
    ],
    'firewalls' => [
        'api' => [
            'pattern' => '^/api',
            'stateless' => true,
            'http_basic' => ['realm' => 'Demo REST API'],
            'json_login' => [
                'check_path' => '/api/login',
            ],
        ],
        'default' => [
            'anonymous' => null,
            'form_login' => null,
        ],
    ],
    'access_control' => [
        ['path' => '^/admin', 'roles' => 'ROLE_ADMIN'],
        ['path' => '^/api', 'roles' => 'ROLE_API'],
    ],
]);
