<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container) {
    // BC layer to avoid deprecation warnings in symfony < 5.3
    if (class_exists(Symfony\Bundle\SecurityBundle\RememberMe\FirewallAwareRememberMeHandler::class)) {
        $tokenAuthenticatorClass = \FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Security\ApiTokenAuthenticator::class;
    } else {
        $tokenAuthenticatorClass = \FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Security\ApiTokenGuardAuthenticator::class;
    }

    $services = $container->services();
    $services->set('api_token_authenticator', $tokenAuthenticatorClass);
};
