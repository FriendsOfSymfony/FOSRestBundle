<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.versioning.listener', \FOS\RestBundle\EventListener\VersionListener::class)
        ->args([
            service('fos_rest.versioning.chain_resolver'),
            '', // default media type version
        ])
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => 33]);

    $services->set('fos_rest.versioning.exclusion_listener', \FOS\RestBundle\EventListener\VersionExclusionListener::class)
        ->args([service('fos_rest.view_handler')])
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => 31]);

    $services->set('fos_rest.versioning.chain_resolver', \FOS\RestBundle\Version\ChainVersionResolver::class)
        ->private()
        ->args([
            [], // resolvers
        ]);

    $services->set('fos_rest.versioning.header_resolver', \FOS\RestBundle\Version\Resolver\HeaderVersionResolver::class)
        ->private()
        ->args([
            '', // request header name
        ]);

    $services->set('fos_rest.versioning.media_type_resolver', \FOS\RestBundle\Version\Resolver\MediaTypeVersionResolver::class)
        ->private()
        ->args([
            '', // regex
        ]);

    $services->set('fos_rest.versioning.query_parameter_resolver', \FOS\RestBundle\Version\Resolver\QueryParameterVersionResolver::class)
        ->private()
        ->args([
            '', // request parameter name
        ]);
};
