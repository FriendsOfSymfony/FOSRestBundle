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
        // TODO move this to the Configuration class as soon as it supports setting such a default
        array_unshift($configs, array(
            'formats' => array(
                'json'  => 'fos_rest.decoder.json',
                'xml'   => 'fos_rest.decoder.xml',
                'html'  => 'templating',
            ),
            'force_redirects' => array(
                'html'  => true,
            ),
        ));

        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('view.xml');
        $loader->load('routing.xml');

        $container->setAlias($this->getAlias().'.view_handler', $config['service']['view_handler']);
        $container->setParameter($this->getAlias().'.formats', $config['formats']);
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

            $container->getDefinition($config['service']['body_listener'])->addTag('kernel.event_listener', array(
                'event' => 'kernel.request',
                'method' => 'onKernelRequest'
            ));
            $container->setParameter($this->getAlias().'.decoders', $config['body_listener']['decoders']);
        }

        if (!empty($config['format_listener'])) {
            $loader->load('format_listener.xml');

            $container->getDefinition($config['service']['format_listener'])->addTag('kernel.event_listener', array(
                'event' => 'kernel.controller',
                'method' => 'onKernelController'
            ));
            $container->setParameter($this->getAlias().'.default_priorities', $config['format_listener']['default_priorities']);
            $container->setParameter($this->getAlias().'.fallback_format', $config['format_listener']['fallback_format']);
        }
        
        if (!empty($config['flash_message_listener'])) {
            $loader->load('flash_message_listener.xml');

            $container->getDefinition($config['service']['flash_message_listener'])->addTag('kernel.event_listener', array(
                'event' => 'kernel.response',
                'method' => 'onKernelResponse'
            ));
            $container->setParameter($this->getAlias().'.flash_message_listener.options', $config['flash_message_listener']);
        }

        if ($config['frameworkextra_bundle']) {
            $loader->load('frameworkextra_bundle.xml');

            $container->getDefinition($config['service']['view_response_listener'])
                ->addTag('kernel.event_listener', array(
                    'event' => 'kernel.controller',
                    'method' => 'onKernelController'
                ))
                ->addTag('kernel.event_listener', array(
                    'event' => 'kernel.view',
                    'method' => 'onKernelView',
                    'priority' => 100
                ));
        }
    }
}
