<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

return function (Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator $container): void {
    if (class_exists(Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator::class) && method_exists(Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator::class, 'createToken')) {
        // Authenticator for use on Symfony 5.4 and newer
        $tokenAuthenticatorClass = \FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Security\ApiTokenAuthenticator::class;
    } elseif (class_exists(Symfony\Bundle\SecurityBundle\RememberMe\FirewallAwareRememberMeHandler::class)) {
        // Authenticator for use on Symfony 5.3
        $tokenAuthenticatorClass = \FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Security\ApiToken53Authenticator::class;
    } else {
        // Authenticator for use on Symfony 5.2 and earlier
        $tokenAuthenticatorClass = \FOS\RestBundle\Tests\Functional\Bundle\TestBundle\Security\ApiTokenGuardAuthenticator::class;
    }

    $services = $container->services();
    $services->set('api_token_authenticator', $tokenAuthenticatorClass);
};
