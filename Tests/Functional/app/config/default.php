<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Symfony\Component\HttpKernel\Kernel;

$container->loadFromExtension('fos_rest', [
    'exception' => [
        'exception_controller' => Kernel::VERSION_ID >= 40100 ? 'fos_rest.exception.controller::showAction' : 'fos_rest.exception.controller:showAction',
    ],
]);
