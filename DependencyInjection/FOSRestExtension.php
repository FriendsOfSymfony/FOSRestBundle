<?php

namespace FOS\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor,
    Symfony\Component\Config\FileLocator,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\Reference,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/*
 * This file is part of the FOSRestBundle
 *
 * (c) Lukas Kahwe Smith <smith@pooteeweet.org>
 * (c) Konstantin Kudryashov <ever.zet@gmail.com>
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

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
                'json'  => 'fos_rest.json',
                'xml'   => 'fos_rest.xml',
                'html'  => 'fos_rest.html',
            ),
        ));

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('view.xml');
        $loader->load('routing.xml');

        $container->setParameter($this->getAlias().'.formats', $config['formats']);
        $container->setParameter($this->getAlias().'.default_form_key', $config['default_form_key']);

        foreach ($config['classes'] as $key => $value) {
            $container->setParameter($this->getAlias().'.'.$key.'.class', $value);
        }

        if ($config['serializer_bundle']) {
            foreach ($config['formats'] as $format => $encoder) {
                $encoder = $container->getDefinition($encoder);
                $encoder->addTag('jms_serializer.encoder', array('format' => $format));
            }

            $priority = count($config['normalizers']);
            foreach ($config['normalizers'] as $normalizer) {
                $normalizer = $container->getDefinition($normalizer);
                $normalizer->addTag('jms_serializer.normalizer', array('priority' => $priority--));
            }

            $container->setAlias('fos_rest.serializer', 'serializer');
        } else {
            $serializer = $container->getDefinition('fos_rest.serializer');

            $encoders = array();
            foreach ($config['formats'] as $format => $encoder) {
                $encoders[$format] = new Reference($encoder);
            }
            $serializer->replaceArgument(0, $encoders);

            $normalizers = array();
            foreach ($config['normalizers'] as $normalizer) {
                $normalizers[] = new Reference($normalizer);
            }
            $serializer->replaceArgument(1, $normalizers);
        }

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

        if ($config['body_listener']) {
            $loader->load('body_listener.xml');
        }

        if (isset($config['format_listener'])) {
            $loader->load('format_listener.xml');
            $container->setParameter($this->getAlias().'.default_priorities', $config['format_listener']['default_priorities']);
            $container->setParameter($this->getAlias().'.fallback_format', $config['format_listener']['fallback_format']);
        }
        
        $container->setParameter($this->getAlias().'.routing.loader.default_format', $config['routing_loader']['default_format']);

        if ($config['frameworkextra_bundle']) {
            $loader->load('frameworkextra_bundle.xml');
        }

        foreach ($config['services'] as $key => $value) {
            if (isset($value)) {
                $container->setAlias($this->getAlias().'.'.$key, $value);
            }
        }
    }
}
