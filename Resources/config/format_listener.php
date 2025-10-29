<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.format_listener', \FOS\RestBundle\EventListener\FormatListener::class)
        ->args([service('fos_rest.format_negotiator')])
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => 34]);

    $services->set('fos_rest.format_negotiator', \FOS\RestBundle\Negotiation\FormatNegotiator::class)
        ->args([service('request_stack')]);
};
