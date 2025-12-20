<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.request.param_fetcher', \FOS\RestBundle\Request\ParamFetcher::class)
        ->args([
            service('service_container'),
            service('fos_rest.request.param_fetcher.reader'),
            service('request_stack'),
            service('validator')->nullOnInvalid(),
        ]);

    $services->alias(\FOS\RestBundle\Request\ParamFetcherInterface::class, 'fos_rest.request.param_fetcher');

    $services->set('fos_rest.request.param_fetcher.reader', \FOS\RestBundle\Request\ParamReader::class)
        ->private()
        ->args([service('annotation_reader')->nullOnInvalid()]);
};
