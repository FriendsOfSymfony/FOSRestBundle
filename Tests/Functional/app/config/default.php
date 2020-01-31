<?php

$container->loadFromExtension('fos_rest', [
    'exception' => [
        'exception_controller' => 'fos_rest.exception.controller::showAction',
    ],
]);
