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

use Symfony\Component\Config\Definition\Processor,
    Symfony\Component\Config\FileLocator,
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

        $processor = new Processor();
        $configuration = new Configuration();
        $config = $processor->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('view.xml');
        $loader->load('routing.xml');

        $container->setParameter($this->getAlias().'.formats', $config['formats']);
        $container->setParameter($this->getAlias().'.decoders', $config['formats']);
        $container->setParameter($this->getAlias().'.default_form_key', $config['default_form_key']);

        foreach ($config['classes'] as $key => $value) {
            $container->setParameter($this->getAlias().'.'.$key.'.class', $value);
        }

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

        if (isset($config['body_listener'])) {
            $loader->load('body_listener.xml');
        }

        if (isset($config['format_listener'])) {
            $loader->load('format_listener.xml');
            $container->setParameter($this->getAlias().'.default_priorities', $config['format_listener']['default_priorities']);
            $container->setParameter($this->getAlias().'.fallback_format', $config['format_listener']['fallback_format']);
        }
        
        if (isset($config['flash_message_listener'])) {
            $container->setParameter($this->getAlias().'.flash_message_listener.options', $config['flash_message_listener']);

            $loader->load('flash_message_listener.xml');
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
