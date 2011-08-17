<?php

/*
 * This file is part of the FOSRestBundle package.
 *
 * (c) FriendsOfSymfony <http://friendsofsymfony.github.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FOS\RestBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

use FOS\RestBundle\Response\Codes;

class FOSRestExtension extends Extension
{
    /**
     * Loads the services based on your application configuration.
     *
     * @param array $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('view.xml');
        $loader->load('routing.xml');

        $formats = array_merge(array_fill_keys($config['formats'], false), array_fill_keys($config['templating_formats'], true));

        $container->setAlias($this->getAlias().'.view_handler', $config['service']['view_handler']);
        $container->setParameter($this->getAlias().'.formats', $formats);
        $container->setParameter($this->getAlias().'.routing.loader.default_format', $config['routing_loader']['default_format']);

        foreach ($config['force_redirects'] as $format => $code) {
            if (true === $code) {
                $config['force_redirects'][$format] = Codes::HTTP_FOUND;
            }
        }
        $container->setParameter($this->getAlias().'.force_redirects', $config['force_redirects']);

        foreach ($config['exception']['codes'] as $exception => $code) {
            if (is_string($code)) {
                $config['exception']['codes'][$exception] = constant("\FOS\RestBundle\Response\Codes::$code");
            }
        }
        $container->setParameter($this->getAlias().'.exception.codes', $config['exception']['codes']);
        $container->setParameter($this->getAlias().'.exception.messages', $config['exception']['messages']);

        if (is_string($config['failed_validation'])) {
            $config['failed_validation'] = constant('\FOS\RestBundle\Response\Codes::'.$config['failed_validation']);
        }
        $container->setParameter($this->getAlias().'.failed_validation', $config['failed_validation']);

        if (!empty($config['body_listener'])) {
            $loader->load('body_listener.xml');

            $container->setParameter($this->getAlias().'.decoders', $config['body_listener']['decoders']);
        }

        if (!empty($config['format_listener'])) {
            $loader->load('format_listener.xml');

            $container->setParameter($this->getAlias().'.default_priorities', $config['format_listener']['default_priorities']);
            $container->setParameter($this->getAlias().'.fallback_format', $config['format_listener']['fallback_format']);
        }
        
        if (!empty($config['flash_message_listener'])) {
            $loader->load('flash_message_listener.xml');

            $container->setParameter($this->getAlias().'.flash_message_listener.options', $config['flash_message_listener']);
        }

        if ($config['view_response_listener']) {
            $loader->load('view_response_listener.xml');
        }
    }
}
