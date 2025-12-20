<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.param_fetcher_listener', \FOS\RestBundle\EventListener\ParamFetcherListener::class)
        ->args([
            service('fos_rest.request.param_fetcher'),
            false,
        ])
        ->tag('kernel.event_listener', ['event' => 'kernel.controller', 'method' => 'onKernelController', 'priority' => 5]);
};
