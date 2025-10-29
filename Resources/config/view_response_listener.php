<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.view_response_listener', \FOS\RestBundle\EventListener\ViewResponseListener::class)
        ->args([
            service('fos_rest.view_handler'),
            '', // force view
            service('annotation_reader')->nullOnInvalid(),
        ])
        ->tag('kernel.event_subscriber');
};
