<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.exception.codes_map', \FOS\RestBundle\Util\ExceptionValueMap::class)
        ->private()
        ->args([
            [], // exception codes
        ]);

    $services->set('fos_rest.exception.messages_map', \FOS\RestBundle\Util\ExceptionValueMap::class)
        ->private()
        ->args([
            [], // exception messages
        ]);

    $services->set('fos_rest.serializer.flatten_exception_handler', \FOS\RestBundle\Serializer\Normalizer\FlattenExceptionHandler::class)
        ->private()
        ->args([
            service('fos_rest.exception.codes_map'),
            service('fos_rest.exception.messages_map'),
            '', // show exception message
            '', // render according to RFC 7807
        ])
        ->tag('jms_serializer.subscribing_handler');

    $services->set('fos_rest.serializer.flatten_exception_normalizer', \FOS\RestBundle\Serializer\Normalizer\FlattenExceptionNormalizer::class)
        ->private()
        ->args([
            service('fos_rest.exception.codes_map'), // exception messages
            service('fos_rest.exception.messages_map'), // exception messages
            '', // show exception message
            '', // render according to RFC 7807
        ])
        ->tag('serializer.normalizer', ['priority' => -10]);
};
