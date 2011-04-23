<?php

namespace FOS\RestBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor,
    Symfony\Component\HttpKernel\DependencyInjection\Extension,
    Symfony\Component\DependencyInjection\Loader\XmlFileLoader,
    Symfony\Component\DependencyInjection\ContainerBuilder,
    Symfony\Component\Config\FileLocator;

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
            'exception' => array(
                'codes' => array('*' => 500),
                'messages' => array('*' => false),
            )
        ));

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = $this->getFileLoader($container);
        $loader->load('view.xml');
        $loader->load('routing.xml');

        foreach ($config['class'] as $key => $value) {
            $container->setParameter($this->getAlias().'.'.$key.'.class', $value);
        }

        $container->setParameter($this->getAlias().'.formats', $config['formats']);

        foreach ($config['exception']['codes'] as $exception => $code) {
            if (is_string($code)) {
                $config['exception']['codes'][$exception] = constant("\FOS\RestBundle\Response\Codes::$code");
            }
        }

        $container->setParameter($this->getAlias().'.exception.codes', $config['exception']['codes']);

        $container->setParameter($this->getAlias().'.exception.messages', $config['exception']['messages']);

        if (!empty($config['format_listener'])) {
            $loader->load('request_format_listener.xml');
            $container->setParameter($this->getAlias().'.detect_format', $config['format_listener']['detect_format']);
            $container->setParameter($this->getAlias().'.decode_body', $config['format_listener']['decode_body']);
            $container->setParameter($this->getAlias().'.default_format', $config['format_listener']['default_format']);
        }

        if (!empty($config['frameworkextra'])) {
            $loader->load('frameworkextra.xml');
        }

        foreach ($config['service'] as $key => $value) {
            if (isset($value)) {
                $container->setAlias($this->getAlias().'.'.$key, $value);
            }
        }
    }

    /**
     * Get File Loader
     *
     * @param ContainerBuilder $container
     */
    public function getFileLoader($container)
    {
        return new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    }
}
