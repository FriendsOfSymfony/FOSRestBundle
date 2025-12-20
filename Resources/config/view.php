<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.view_handler.default', \FOS\RestBundle\View\ViewHandler::class)
        ->private()
        ->tag('kernel.reset', ['method' => 'reset']);

    $services->alias(\FOS\RestBundle\View\ViewHandlerInterface::class, 'fos_rest.view_handler');

    $services->set('fos_rest.view_handler.jsonp', \FOS\RestBundle\View\JsonpHandler::class)
        ->private()
        ->args([
            '', // JSONP callback parameter
        ]);
};
