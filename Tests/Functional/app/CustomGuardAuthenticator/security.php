<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$securityConfig = [
    'providers' => [
        'in_memory' => [
            'memory' => [],
        ],
    ],
    'access_control' => [
        ['path' => '^/api/comments', 'roles' => 'ROLE_ADMIN'],
        ['path' => '^/api', 'roles' => 'ROLE_API'],
    ],
];

$passwordHasherConfig = ['Symfony\Component\Security\Core\User\User' => 'plaintext'];

// BC layer to avoid deprecation warnings in symfony/security-bundle < 5.3
if (class_exists(\Symfony\Bundle\SecurityBundle\RememberMe\FirewallAwareRememberMeHandler::class)) {
    $securityConfig['password_hashers'] = $passwordHasherConfig;
    $securityConfig['enable_authenticator_manager'] = true;
    $securityConfig['firewalls'] = [
        'default' => [
            'provider' => 'in_memory',
            'stateless' => true,
            'custom_authenticators' => ['api_token_authenticator'],
        ],
    ];
} else {
    $securityConfig['encoders'] = $passwordHasherConfig;
    $securityConfig['firewalls'] = [
        'default' => [
            'provider' => 'in_memory',
            'stateless' => true,
            'guard' => [
                'authenticators' => [
                    'api_token_authenticator',
                ],
            ],
        ],
    ];
}

$container->loadFromExtension('security', $securityConfig);
