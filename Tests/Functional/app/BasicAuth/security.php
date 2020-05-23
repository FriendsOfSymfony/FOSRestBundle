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
                ],
            ],
        ],
    ],
    'firewalls' => [
        'default' => [
            'provider' => 'in_memory',
            'stateless' => true,
            'http_basic' => null,
        ],
    ],
    'access_control' => [
        ['path' => '^/api/comments', 'roles' => 'ROLE_ADMIN'],
        ['path' => '^/api', 'roles' => 'ROLE_API'],
    ],
]);
