<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.converter.request_body', \FOS\RestBundle\Request\RequestBodyParamConverter::class)
        ->args([
            service('fos_rest.serializer'),
            [], // serializer exclusion strategy groups
            '', // serializer exclusion strategy version
            service('fos_rest.validator')->ignoreOnInvalid(),
            null, // request body validation errors argument
        ])
        ->tag('request.param_converter', ['converter' => 'fos_rest.request_body', 'priority' => -50]);
};
