<?php

use Symfony\Component\HttpKernel\Kernel;

$container->loadFromExtension('fos_rest', [
    'exception' => [
        'exception_controller' => Kernel::VERSION_ID >= 40100 ? 'fos_rest.exception.controller::showAction' : 'fos_rest.exception.controller:showAction',
    ],
]);
