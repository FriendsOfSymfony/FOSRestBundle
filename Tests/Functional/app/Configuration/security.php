<?php

use Symfony\Component\Security\Core\Security;

$defaultFirewall = [
    'anonymous' => null,
];

if (method_exists(Security::class, 'getUser')) {
    $defaultFirewall['logout_on_user_change'] = true;
}

$container->loadFromExtension('security', [
    'providers' => [
        'in_memory' => [
            'memory' => null,
        ],
    ],
    'firewalls' => [
        'default' => $defaultFirewall,
    ],
]);
