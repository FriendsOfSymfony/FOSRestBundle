<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services();
    $parameters = $container->parameters();

    $services->set('fos_rest.form.extension.csrf_disable', \FOS\RestBundle\Form\Extension\DisableCSRFExtension::class)
        ->args([
            service('security.token_storage'),
            '', // disable CSRF role
            service('security.authorization_checker'),
        ]);
};
