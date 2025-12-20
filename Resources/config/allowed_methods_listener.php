<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.allowed_methods_listener', \FOS\RestBundle\EventListener\AllowedMethodsListener::class)
        ->args([service('fos_rest.allowed_methods_loader')])
        ->tag('kernel.event_listener', ['event' => 'kernel.response', 'method' => 'onKernelResponse']);

    $services->set('fos_rest.allowed_methods_loader', \FOS\RestBundle\Response\AllowedMethodsLoader\AllowedMethodsRouterLoader::class)
        ->private()
        ->args([
            service('router'),
            '',
            '%kernel.debug%',
        ])
        ->tag('kernel.cache_warmer');
};
