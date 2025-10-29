<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.serializer.jms', \FOS\RestBundle\Serializer\JMSSerializerAdapter::class)
        ->private()
        ->args([
            service('jms_serializer.serializer'),
            service('jms_serializer.serialization_context_factory'),
            service('jms_serializer.deserialization_context_factory'),
        ]);

    $services->set('fos_rest.serializer.symfony', \FOS\RestBundle\Serializer\SymfonySerializerAdapter::class)
        ->private()
        ->args([service('serializer')]);

    // Normalizes FormInterface when using the symfony serializer
    $services->set('fos_rest.serializer.form_error_normalizer', \FOS\RestBundle\Serializer\Normalizer\FormErrorNormalizer::class)
        ->private()
        ->tag('serializer.normalizer', ['priority' => -10]);
};
