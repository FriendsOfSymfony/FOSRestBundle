<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\TemplateListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;

/**
 * Checks if the SensioFrameworkExtraBundle views annotations are disabled when using the View Response listener.
 *
 * @author Eriksen Costa <eriksencosta@gmail.com>
 *
 * @internal
 */
final class ConfigurationCheckPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->has('fos_rest.converter.request_body') && !($container->has('sensio_framework_extra.converter.listener') || $container->has(ParamConverterListener::class))) {
            throw new \RuntimeException('You need to enable the parameter converter listeners in SensioFrameworkExtraBundle when using the FOSRestBundle RequestBodyParamConverter');
        }

        if ($container->has('fos_rest.view_response_listener') && isset($container->getParameter('kernel.bundles')['SensioFrameworkExtraBundle'])) {
            if (!($container->has('sensio_framework_extra.view.listener') || $container->has(TemplateListener::class))) {
                throw new \RuntimeException('You must enable the SensioFrameworkExtraBundle view annotations to use the ViewResponseListener. Did you forget to install and enable the TwigBundle?');
            }
        }

        if (!$container->has((string) $container->getAlias('fos_rest.templating'))) {
            $container->removeAlias('fos_rest.templating');
        }
    }
}
