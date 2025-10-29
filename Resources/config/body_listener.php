<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.normalizer.camel_keys', \FOS\RestBundle\Normalizer\CamelKeysNormalizer::class);

    $services->set('fos_rest.normalizer.camel_keys_with_leading_underscore', \FOS\RestBundle\Normalizer\CamelKeysNormalizerWithLeadingUnderscore::class);

    $services->set('fos_rest.decoder.json', \FOS\RestBundle\Decoder\JsonDecoder::class);

    $services->set('fos_rest.decoder.jsontoform', \FOS\RestBundle\Decoder\JsonToFormDecoder::class);

    $services->set('fos_rest.decoder.xml', \FOS\RestBundle\Decoder\XmlDecoder::class);

    $services->set('fos_rest.decoder_provider', \FOS\RestBundle\Decoder\ContainerDecoderProvider::class)
        ->args([
            service('service_container'),
            [],
        ]);

    $services->set('fos_rest.body_listener', \FOS\RestBundle\EventListener\BodyListener::class)
        ->args([
            service('fos_rest.decoder_provider'),
            '', // exception on unsupported content type
        ])
        ->tag('kernel.event_listener', ['event' => 'kernel.request', 'method' => 'onKernelRequest', 'priority' => 10]);
};
