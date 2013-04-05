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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\HttpKernel\Kernel;

use FOS\Rest\Util\Codes;

use FOS\RestBundle\FOSRestBundle;

class FOSRestExtension extends Extension
{
    /**
     * Loads the services based on your application configuration.
     *
     * @param array            $configs
     * @param ContainerBuilder $container
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('view.xml');
        $loader->load('routing.xml');
        $loader->load('util.xml');
        $loader->load('request.xml');

        $container->setParameter('fos_rest.cache_dir', $config['cache_dir']);

        $formats = array();
        foreach ($config['view']['formats'] as $format => $enabled) {
            if ($enabled) {
                $formats[$format] = false;
            }
        }
        foreach ($config['view']['templating_formats'] as $format => $enabled) {
            if ($enabled) {
                $formats[$format] = true;
            }
        }

        foreach ($config['service'] as $key => $service) {
            $container->setAlias($this->getAlias().'.'.$key, $config['service'][$key]);
        }

        if (!empty($config['serializer']['version'])) {
            $container->setParameter($this->getAlias().'.serializer.exclusion_strategy.version', $config['serializer']['version']);
        }
        if (!empty($config['serializer']['groups'])) {
            $container->setParameter($this->getAlias().'.serializer.exclusion_strategy.groups', $config['serializer']['groups']);
        }

        $container->setParameter($this->getAlias().'.formats', $formats);
        $container->setParameter($this->getAlias().'.default_engine', $config['view']['default_engine']);

        foreach ($config['view']['force_redirects'] as $format => $code) {
            if (true === $code) {
                $config['view']['force_redirects'][$format] = Codes::HTTP_FOUND;
            }
        }
        $container->setParameter($this->getAlias().'.force_redirects', $config['view']['force_redirects']);

        if (!is_numeric($config['view']['failed_validation'])) {
            $config['view']['failed_validation'] = constant('\FOS\Rest\Util\Codes::'.$config['view']['failed_validation']);
        }
        $container->setParameter($this->getAlias().'.failed_validation', $config['view']['failed_validation']);

        if (!is_numeric($config['view']['empty_content'])) {
            $config['view']['empty_content'] = constant('\FOS\Rest\Util\Codes::'.$config['view']['empty_content']);
        }
        $container->setParameter($this->getAlias().'.empty_content', $config['view']['empty_content']);

        $container->setParameter($this->getAlias().'.serialize_null', $config['view']['serialize_null']);

        if (!empty($config['view']['view_response_listener'])) {
            $loader->load('view_response_listener.xml');
            $container->setParameter($this->getAlias().'.view_response_listener.force_view', 'force' === $config['view']['view_response_listener']);
        }

        $container->setParameter($this->getAlias().'.routing.loader.default_format', $config['routing_loader']['default_format']);
        $container->setParameter($this->getAlias().'.routing.loader.include_format', $config['routing_loader']['include_format']);

        foreach ($config['exception']['codes'] as $exception => $code) {
            if (!is_numeric($code)) {
                $config['exception']['codes'][$exception] = constant("\FOS\Rest\Util\Codes::$code");
            }
            $this->testExceptionExists($exception);
        }
        foreach ($config['exception']['messages'] as $exception => $message) {
            $this->testExceptionExists($exception);
        }

        $container->setParameter($this->getAlias().'.exception.codes', $config['exception']['codes']);
        $container->setParameter($this->getAlias().'.exception.messages', $config['exception']['messages']);

        if (!empty($config['body_listener'])) {
            $loader->load('body_listener.xml');

            $container->setParameter($this->getAlias().'.decoders', $config['body_listener']['decoders']);
        }

        if (!empty($config['format_listener'])) {
            $loader->load('format_listener.xml');

            $container->setParameter($this->getAlias().'.default_priorities', $config['format_listener']['default_priorities']);
            $container->setParameter($this->getAlias().'.prefer_extension', $config['format_listener']['prefer_extension']);
            $container->setParameter($this->getAlias().'.fallback_format', $config['format_listener']['fallback_format']);
        } else {
            $container->setParameter($this->getAlias().'.default_priorities', array());
            $container->setParameter($this->getAlias().'.prefer_extension', true);
            $container->setParameter($this->getAlias().'.fallback_format', 'html');
        }

        if (!empty($config['view']['mime_types'])) {
            $loader->load('mime_type_listener.xml');

            $container->setParameter($this->getAlias().'.mime_types', $config['view']['mime_types']);
        } else {
            $container->setParameter($this->getAlias().'.mime_types', array());
        }

        if (!empty($config['param_fetcher_listener'])) {
            $loader->load('param_fetcher_listener.xml');

            if ('force' === $config['param_fetcher_listener']) {
                $container->setParameter($this->getAlias().'.param_fetcher_listener.set_params_as_attributes', true);
            }
        }

        if (!empty($config['allowed_methods_listener'])) {
            $loader->load('allowed_methods_listener.xml');
        }

        if (!empty($config['access_denied_listener'])) {
            $loader->load('access_denied_listener.xml');
            $container->setParameter($this->getAlias().'.access_denied_listener.formats', $config['access_denied_listener']);
        }
    }

    /**
     * Check if an exception is loadable.
     *
     * @param  string                   $exception class to test
     * @throws InvalidArgumentException if the class was not found.
     */
    private function testExceptionExists($exception)
    {
        try {
            $reflectionExceptionClass = new \ReflectionClass("\Exception");
            $reflectionExceptionClass->isSubclassOf($exception);
        } catch (\ReflectionException $re) {
            throw new \InvalidArgumentException("FOSRestBundle exception mapper: Could not load class $exception. Most probably a problem with your configuration.");
        }
    }
}
